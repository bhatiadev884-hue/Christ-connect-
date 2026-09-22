<?php
/**
 * Google AI Studio (Gemini 1.5 Flash) API Integration Module
 * BYOK (Bring Your Own Key) System for Placement Portal
 */

require_once(__DIR__ . "/../db.php");

// ── Ensure site_settings table exists ──
$conn->query("CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

/**
 * Get configured Gemini API Key (User key first, then Site Global key, then ENV)
 */
function getGeminiAPIKey() {
    global $conn;

    // 1. Check User Session BYOK key if set
    if (!empty($_SESSION['user_gemini_api_key'])) {
        return $_SESSION['user_gemini_api_key'];
    }

    // 2. Check Global Site Settings DB
    $res = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key='gemini_api_key'");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (!empty($row['setting_value'])) {
            return trim($row['setting_value']);
        }
    }

    // 3. Fallback to Environment Variable
    return getenv('GEMINI_API_KEY') ?: '';
}

/**
 * Save Global Gemini API Key
 */
function saveGeminiAPIKey($key) {
    global $conn;
    $key = $conn->real_escape_string(trim($key));
    $sql = "INSERT INTO site_settings (setting_key, setting_value) 
            VALUES ('gemini_api_key', '$key') 
            ON CONFLICT(setting_key) DO UPDATE SET setting_value='$key'";
    $res = $conn->query($sql);
    if (!$res) {
        $sql = "INSERT INTO site_settings (setting_key, setting_value) 
                VALUES ('gemini_api_key', '$key') 
                ON DUPLICATE KEY UPDATE setting_value='$key'";
        $res = $conn->query($sql);
    }
    return $res;
}

/**
 * Call Google AI Studio Gemini API (Gemini 1.5 Flash)
 * Uses file_get_contents with stream_context to work on all PHP installations without needing php_curl extension
 */
function callGeminiResumeAI($apiKey, $userProfile, $jobPost) {
    $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];

    $jobTitle = $jobPost['jobtitle'] ?? 'Software Developer';
    $jobDesc  = strip_tags($jobPost['description'] ?? '');
    $jobQual  = $jobPost['qualification'] ?? '';
    $jobExp   = $jobPost['experience'] ?? '';

    $userName = ($userProfile['firstname'] ?? 'Student') . ' ' . ($userProfile['lastname'] ?? '');
    $userSkills = $userProfile['skills'] ?? 'Not specified';
    $userAbout  = $userProfile['aboutme'] ?? 'Not specified';
    $userQual   = $userProfile['qualification'] ?? '';
    $userStream = $userProfile['stream'] ?? '';

    $prompt = "You are an expert HR AI Recruiter for campus placements. Analyze the match between the student's candidate profile and the job drive posting.

JOB DRIVE POSTING:
- Title: {$jobTitle}
- Required Qualification: {$jobQual}
- Experience/Role: {$jobExp}
- Description: {$jobDesc}

STUDENT CANDIDATE PROFILE:
- Name: {$userName}
- Qualification: {$userQual} ({$userStream})
- Technical Skills: {$userSkills}
- About Me / Bio: {$userAbout}

STRICT SCORING MECHANISM MANDATE:
Extract exactly 5 key required technical skills/qualifications from the Job Drive posting.
Compare candidate profile skills against these 5 key required keywords:
- 0 / 5 matched keywords = 0% score
- 1 / 5 matched keywords = 20% score
- 2 / 5 matched keywords = 40% score
- 3 / 5 matched keywords = 60% score
- 4 / 5 matched keywords = 80% score
- 5 / 5 matched keywords = 100% score

Evaluate the candidate and respond ONLY with a valid raw JSON object (no markdown formatting, no code blocks) with exact keys:
{
  \"score\": (integer 0, 20, 40, 60, 80, or 100),
  \"verdict\": \"(e.g., 🔥 High Shortlist Chance OR ⚡ Moderate Shortlist Chance OR ⚠️ Low Shortlist Chance)\",
  \"readiness\": \"(e.g., 🚀 Ready to Submit! OR ⚡ Good Fit - Optional Tweaks OR 🛑 Update Resume Before Applying)\",
  \"message\": \"(2-3 sentences concise professional AI evaluation summarizing candidate suitability)\",
  \"matched_keywords\": [\"List\", \"Of\", \"Matched\", \"Skills\"],
  \"missing_keywords\": [\"List\", \"Of\", \"Missing\", \"Critical\", \"Skills\"],
  \"recommendations\": [\"Actionable tip 1\", \"Actionable tip 2\", \"Actionable tip 3\"]
}";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "responseMimeType" => "application/json"
        ]
    ];

    $jsonData = json_encode($payload);
    $lastError = '';

    foreach ($modelsToTry as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($jsonData) . "\r\n",
                "content" => $jsonData,
                "timeout" => 12,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
        }

        if (!$response) continue;

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $lastError = $json['error']['message'] ?? 'API error';
            continue;
        }

        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/^```\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/i', '', $rawText);

        $aiResult = json_decode($rawText, true);

        if (is_array($aiResult) && isset($aiResult['score'])) {
            return $aiResult;
        }
    }

    return ['error' => 'Gemini API Error: ' . ($lastError ?: 'Unable to connect to Google Gemini API server')];
}

/**
 * Call Google AI Studio Gemini API for AI Skill Gap Analysis & Roadmap
 */
function callGeminiSkillGapAI($apiKey, $userProfile, $targetRole = 'Software Developer') {
    $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];

    $targetRole = trim($targetRole) ?: 'Software Developer';
    $userName   = ($userProfile['firstname'] ?? 'Student') . ' ' . ($userProfile['lastname'] ?? '');
    $userSkills = $userProfile['skills'] ?? 'Not specified';
    $userAbout  = $userProfile['aboutme'] ?? 'Not specified';
    $userQual   = $userProfile['qualification'] ?? '';
    $userStream = $userProfile['stream'] ?? '';

    $prompt = "You are an expert AI Career Coach and Tech Recruiter. Perform an AI Skill Gap Analysis for a student targeting a specific job role.

TARGET ROLE:
- Role: {$targetRole}

STUDENT PROFILE:
- Name: {$userName}
- Qualification: {$userQual} ({$userStream})
- Current Skills Listed: {$userSkills}
- Profile Summary: {$userAbout}

TASKS:
1. Identify skills the student ALREADY HAS that are relevant to '{$targetRole}'.
2. Identify critical skills the student NEEDS TO IMPROVE / IS MISSING for '{$targetRole}'.
3. Calculate an overall match/readiness percentage (0 to 100).
4. Create a realistic, step-by-step recommended learning roadmap (3 phases/weeks).
5. Suggest top 2 certifications and 2 portfolio project ideas to bridge the gap.

Respond ONLY with a raw JSON object (no markdown formatting, no code fences):
{
  \"target_role\": \"{$targetRole}\",
  \"match_percentage\": (integer 0 to 100),
  \"verdict\": \"(e.g., 🚀 High Role Readiness / ⚡ Moderate Skill Gap / 🛑 Significant Skill Gap)\",
  \"already_have\": [\"Skill1\", \"Skill2\", \"Skill3\"],
  \"need_to_improve\": [\"MissingSkill1\", \"MissingSkill2\", \"MissingSkill3\"],
  \"summary\": \"(2-3 sentences career coach overview of candidate readiness for {$targetRole})\",
  \"roadmap\": [
    {
      \"phase\": \"Phase 1: Core Technologies & Tools (Weeks 1-2)\",
      \"title\": \"Title of Phase 1\",
      \"skills\": [\"SkillA\", \"SkillB\"],
      \"description\": \"Actionable steps and learning objectives for Phase 1.\"
    },
    {
      \"phase\": \"Phase 2: Frameworks & System Building (Weeks 3-4)\",
      \"title\": \"Title of Phase 2\",
      \"skills\": [\"SkillC\", \"SkillD\"],
      \"description\": \"Actionable steps and learning objectives for Phase 2.\"
    },
    {
      \"phase\": \"Phase 3: Portfolio & Deployment (Weeks 5-6)\",
      \"title\": \"Title of Phase 3\",
      \"skills\": [\"SkillE\", \"SkillF\"],
      \"description\": \"Actionable steps and learning objectives for Phase 3.\"
    }
  ],
  \"recommended_certifications\": [\"Cert 1\", \"Cert 2\"],
  \"recommended_projects\": [\"Project Idea 1\", \"Project Idea 2\"]
}";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "responseMimeType" => "application/json"
        ]
    ];

    $jsonData = json_encode($payload);
    $lastError = '';

    foreach ($modelsToTry as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($jsonData) . "\r\n",
                "content" => $jsonData,
                "timeout" => 12,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
        }

        if (!$response) continue;

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $lastError = $json['error']['message'] ?? 'API error';
            continue;
        }

        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/^```\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/i', '', $rawText);

        $aiResult = json_decode($rawText, true);

        if (is_array($aiResult) && isset($aiResult['already_have'])) {
            return $aiResult;
        }
    }

    return ['error' => 'Gemini API Error: ' . ($lastError ?: 'Unable to connect to Google Gemini API server')];
}

/**
 * Call Google AI Studio Gemini API for AI Company/Job Recommendations
 */
function callGeminiJobRecommendationAI($apiKey, $userProfile, $jobPosts) {
    $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];

    $userName     = ($userProfile['firstname'] ?? 'Student') . ' ' . ($userProfile['lastname'] ?? '');
    $userSkills   = $userProfile['skills'] ?? 'Not specified';
    $userAbout    = $userProfile['aboutme'] ?? 'Not specified';
    $userQual     = $userProfile['qualification'] ?? 'MCA';
    $userStream   = $userProfile['stream'] ?? 'Computer Science';
    $userUg       = $userProfile['ug'] ?? '80';
    $userPg       = $userProfile['pg'] ?? '80';
    $userPrefRole = $userProfile['designation'] ?? 'Software Engineer';

    $jobsJson = json_encode($jobPosts);

    $prompt = "You are an AI Campus Placement Matchmaker and Corporate Recruiter.
Analyze the candidate profile against the list of available placement drive postings and rank them by suitability.

CANDIDATE EVALUATION CRITERIA:
1. Technical Skills: {$userSkills}
2. Degree & Stream: {$userQual} ({$userStream})
3. Academic Scores / CGPA: UG: {$userUg}%, PG: {$userPg}%
4. Projects & Experience: {$userAbout}
5. Preferred Role: {$userPrefRole}

AVAILABLE PLACEMENT DRIVES:
{$jobsJson}

TASKS:
Evaluate each placement drive and output a raw JSON array of recommendation objects sorted from highest match_score to lowest match_score:
[
  {
    \"id_jobpost\": (integer id),
    \"match_score\": (integer 0 to 100),
    \"verdict\": \"(e.g., 🔥 Highly Recommended / ⚡ Good Fit / ⚠️ Fair Fit)\",
    \"match_reasons\": [
      \"Reason 1 comparing skills/degree/CGPA\",
      \"Reason 2 comparing projects/preferred role\"
    ],
    \"key_matching_skills\": [\"Skill1\", \"Skill2\"],
    \"eligibility_status\": \"(e.g., Eligible - Meets Degree & CGPA Cutoff)\"
  }
]

Respond ONLY with raw JSON array (no markdown formatting, no code blocks).";

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.2,
            "responseMimeType" => "application/json"
        ]
    ];

    $jsonData = json_encode($payload);
    $lastError = '';

    foreach ($modelsToTry as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($jsonData) . "\r\n",
                "content" => $jsonData,
                "timeout" => 12,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer"      => false,
                "verify_peer_name" => false
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
        }

        if (!$response) continue;

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $lastError = $json['error']['message'] ?? 'API error';
            continue;
        }

        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/^```\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/i', '', $rawText);

        $aiResult = json_decode($rawText, true);

        if (is_array($aiResult) && !empty($aiResult)) {
            return $aiResult;
        }
    }

    return ['error' => 'Gemini API Error: ' . ($lastError ?: 'Unable to connect to Google Gemini API server')];
}

/**
 * Call Google AI Studio Gemini API for AI Career Assistant Chatbot
 */
function callGeminiCareerAssistantAI($apiKey, $userProfile, $userMessage, $activeDrives = []) {
    $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];

    $userName   = ($userProfile['firstname'] ?? 'Student') . ' ' . ($userProfile['lastname'] ?? '');
    $userSkills = $userProfile['skills'] ?? 'Not specified';
    $userAbout  = $userProfile['aboutme'] ?? 'Not specified';
    $userQual   = $userProfile['qualification'] ?? 'MCA';
    $userStream = $userProfile['stream'] ?? 'Computer Science';
    $userUg     = $userProfile['ug'] ?? '80';
    $userPg     = $userProfile['pg'] ?? '80';

    $drivesSummary = "";
    if (!empty($activeDrives)) {
        foreach ($activeDrives as $d) {
            $company = $d['companyname'] ?? 'Company';
            $title   = $d['jobtitle'] ?? 'Role';
            $qual    = $d['qualification'] ?? 'Any';
            $cutoff  = $d['maximumsalary'] ?? '60';
            $drivesSummary .= "- {$company}: {$title} (Required: {$qual}, Academic Cutoff: {$cutoff}%)\n";
        }
    } else {
        $drivesSummary = "No active drive postings available currently.";
    }

    $prompt = "You are PlaceMentor AI, an expert, friendly AI Placement Advisor and Career Assistant for campus recruitment.

STUDENT PROFILE CONTEXT:
- Name: {$userName}
- Qualification: {$userQual} ({$userStream})
- Academic Scores: UG: {$userUg}%, PG: {$userPg}%
- Technical Skills: {$userSkills}
- Profile Bio & Experience: {$userAbout}

ACTIVE CAMPUS PLACEMENT DRIVES:
{$drivesSummary}

STUDENT QUESTION:
\"{$userMessage}\"

GUIDELINES FOR ANSWER:
1. Provide a direct, professional, friendly, and highly actionable response in HTML/Markdown format (use bolding, bullet points, clean structure).
2. If asked about eligibility for companies, compare their academic scores ({$userUg}%) and degree ({$userQual}) against the active drive cutoffs listed above.
3. If asked about learning skills for a role (e.g. Java Developer), give a clear step-by-step roadmap.
4. If asked about resume improvement, analyze their skills and profile completeness.
5. If asked about technical interview preparation, offer specific topic lists and mock questions.

Respond with a raw JSON object:
{
  \"reply\": \"(HTML formatted response text)\",
  \"suggested_followups\": [\"Follow-up question 1\", \"Follow-up question 2\"]
}";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["temperature" => 0.3, "responseMimeType" => "application/json"]
    ];

    $jsonData = json_encode($payload);
    $lastError = '';

    foreach ($modelsToTry as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\nContent-Length: " . strlen($jsonData) . "\r\n",
                "content" => $jsonData,
                "timeout" => 12,
                "ignore_errors" => true
            ],
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
        }

        if (!$response) continue;

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $lastError = $json['error']['message'] ?? 'API error';
            continue;
        }

        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/^```\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/i', '', $rawText);

        $res = json_decode($rawText, true);
        if (is_array($res) && isset($res['reply'])) {
            return $res;
        }
    }

    return ['error' => 'Gemini API Error: ' . ($lastError ?: 'Unable to connect to Google Gemini API server')];
}

/**
 * Call Google AI Studio Gemini API for AI Mock Interview Evaluation
 */
function callGeminiMockInterviewEvaluationAI($apiKey, $targetRole, $qaPairs, $userProfile = []) {
    $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];

    $targetRole = trim($targetRole) ?: 'Java Developer';
    $userName   = ($userProfile['firstname'] ?? 'Candidate');
    $qaJson     = json_encode($qaPairs);

    $prompt = "You are a Senior Technical Interviewer & HR Director conducting a comprehensive evaluation of a simulated technical job interview.

TARGET JOB ROLE: {$targetRole}
CANDIDATE NAME: {$userName}

SIMULATED INTERVIEW TRANSCRIPT (Questions Asked & Candidate Answers):
{$qaJson}

TASKS:
Evaluate the candidate's performance across 4 key dimensions:
1. Technical Accuracy Score (0 to 100): Accuracy of technical concepts, algorithms, frameworks, and syntax.
2. Answer Relevance Score (0 to 100): Directness, completeness, and adherence to the question asked.
3. Communication & Tone Score (0 to 100): Clarity, professional phrasing, structured explanation.
4. Overall Overall Score (0 to 100) & Verdict (e.g., 🌟 Strong Hire / ⚡ Moderate Fit - Minor Tweaks / ⚠️ Needs Further Practice).

Provide detailed feedback for EACH question with the ideal answer and specific tips.

Respond ONLY with a raw JSON object (no markdown, no code fences):
{
  \"overall_score\": (integer 0 to 100),
  \"verdict\": \"(e.g., 🌟 Strong Hire - Outstanding Performance)\",
  \"technical_score\": (integer 0 to 100),
  \"relevance_score\": (integer 0 to 100),
  \"communication_score\": (integer 0 to 100),
  \"summary\": \"(2-3 sentences overall interviewer evaluation summary)\",
  \"strengths\": [\"Strength 1\", \"Strength 2\"],
  \"improvements\": [\"Area to improve 1\", \"Area to improve 2\"],
  \"question_evaluations\": [
    {
      \"question\": \"Question 1 text\",
      \"candidate_answer\": \"Answer 1 text\",
      \"score\": (integer 0 to 100),
      \"feedback\": \"Feedback on candidate answer\",
      \"ideal_answer\": \"Ideal structured technical response\"
    }
  ]
}";

    $payload = [
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => ["temperature" => 0.2, "responseMimeType" => "application/json"]
    ];

    $jsonData = json_encode($payload);
    $lastError = '';

    foreach ($modelsToTry as $modelName) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . urlencode($apiKey);

        $options = [
            "http" => [
                "method"  => "POST",
                "header"  => "Content-Type: application/json\r\nContent-Length: " . strlen($jsonData) . "\r\n",
                "content" => $jsonData,
                "timeout" => 14,
                "ignore_errors" => true
            ],
            "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false && function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_TIMEOUT, 14);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
        }

        if (!$response) continue;

        $json = json_decode($response, true);
        if (isset($json['error'])) {
            $lastError = $json['error']['message'] ?? 'API error';
            continue;
        }

        $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
        $rawText = preg_replace('/^```\s*/i', '', $rawText);
        $rawText = preg_replace('/\s*```$/i', '', $rawText);

        $res = json_decode($rawText, true);
        if (is_array($res) && isset($res['overall_score'])) {
            return $res;
        }
    }

    return ['error' => 'Gemini API Error: ' . ($lastError ?: 'Unable to connect to Google Gemini API server')];
}



