<?php
/**
 * AI Resume Shortlisting & Keyword Matcher Engine
 * Powered by Google AI Studio Gemini 1.5 Flash (BYOK System) & Local Keyword Matcher Fallback.
 */

require_once(__DIR__ . "/gemini-api.php");

if (!function_exists('analyzeResumeMatch')) {

    function analyzeResumeMatch($userProfile, $jobPost) {
        // 1. Clean & tokenize text helper
        $cleanText = function($str) {
            $str = strtolower(strip_tags($str));
            $str = preg_replace('/[^a-z0-9\+\#\.\s]/', ' ', $str);
            return array_filter(array_unique(explode(' ', $str)), function($w) {
                return strlen($w) > 2 && !in_array($w, [
                    'and','the','for','with','that','this','from','have','will','your','are','was','were',
                    'been','being','their','them','which','about','into','through','during','before','after',
                    'above','below','from','up','down','in','out','on','off','over','under','again','further'
                ]);
            });
        };

        // Check Resume upload status
        $userResume  = trim($userProfile['resume'] ?? '');
        $hasResume   = !empty($userResume);

        // If NO resume uploaded, return early flag
        if (!$hasResume) {
            return [
                'has_resume' => false,
                'is_gemini'  => false,
                'score'      => 0,
                'verdict'    => '📄 Resume Required',
                'badge_bg'   => '#fee2e2',
                'badge_color'=> '#b91c1c'
            ];
        }

        // ── 2. Check for Google AI Studio Gemini API Key (BYOK System) ──
        $apiKey = getGeminiAPIKey();
        if (!empty($apiKey)) {
            $geminiRes = callGeminiResumeAI($apiKey, $userProfile, $jobPost);
            if (is_array($geminiRes) && !isset($geminiRes['error']) && isset($geminiRes['score'])) {
                $score = round($geminiRes['score']);
                
                if ($score >= 75) {
                    $verdict = $geminiRes['verdict'] ?? "🔥 High Shortlist Chance";
                    $badgeBg = "#dcfce7";
                    $badgeColor = "#15803d";
                } else if ($score >= 50) {
                    $verdict = $geminiRes['verdict'] ?? "⚡ Moderate Shortlist Chance";
                    $badgeBg = "#fef3c7";
                    $badgeColor = "#b45309";
                } else {
                    $verdict = $geminiRes['verdict'] ?? "⚠️ Low Shortlist Chance";
                    $badgeBg = "#fee2e2";
                    $badgeColor = "#b91c1c";
                }

                return [
                    'has_resume'        => true,
                    'is_gemini'         => true,
                    'score'             => $score,
                    'verdict'           => $verdict,
                    'badge_bg'          => $badgeBg,
                    'badge_color'       => $badgeColor,
                    'message'           => $geminiRes['message'] ?? 'Evaluated by Google Gemini 1.5 Flash AI.',
                    'matched_keywords'  => $geminiRes['matched_keywords'] ?? [],
                    'missing_keywords'  => $geminiRes['missing_keywords'] ?? [],
                    'recommendations'   => $geminiRes['recommendations'] ?? []
                ];
            }
        }

        // ── 3. Local Algorithmic Keyword Matcher Fallback ──
        $jobTitle = $jobPost['jobtitle'] ?? '';
        $jobDesc  = $jobPost['description'] ?? '';
        $jobQual  = $jobPost['qualification'] ?? '';
        $jobExp   = $jobPost['experience'] ?? '';

        $jobText  = "$jobTitle $jobDesc $jobQual $jobExp";
        $jobTokens = $cleanText($jobText);

        $techDictionary = [
            'python','java','javascript','c++','c#','php','html','css','sql','mysql','mongodb','postgresql',
            'react','angular','vue','nodejs','express','django','flask','spring','bootstrap','tailwind',
            'aws','cloud','docker','kubernetes','git','github','linux','unix','rest','api','json',
            'machine learning','data science','ai','cybersecurity','networking','oops','dsa','algorithms',
            'database','frontend','backend','fullstack','mobile','android','ios','flutter','react native',
            'mca','btech','bca','bsc','mtech','computer science','information technology'
        ];

        $jobRequiredSkills = [];
        $jobTextLower = strtolower($jobText);
        foreach ($techDictionary as $skill) {
            if (strpos($jobTextLower, $skill) !== false) {
                $jobRequiredSkills[] = $skill;
            }
        }
        if (empty($jobRequiredSkills)) {
            $jobRequiredSkills = array_slice(array_values($jobTokens), 0, 5);
        } else {
            $jobRequiredSkills = array_slice(array_values(array_unique($jobRequiredSkills)), 0, 5);
        }

        $userSkills  = trim($userProfile['skills'] ?? '');
        $userAbout   = trim($userProfile['aboutme'] ?? '');
        $userQual    = trim($userProfile['qualification'] ?? '');
        $userStream  = trim($userProfile['stream'] ?? '');
        $userDesig   = trim($userProfile['designation'] ?? '');

        $userText = "$userSkills $userAbout $userQual $userStream $userDesig";
        $userTextLower = strtolower($userText);

        $studentSkills = [];
        if (!empty($userSkills) || !empty($userTextLower)) {
            foreach ($techDictionary as $skill) {
                if (strpos($userTextLower, $skill) !== false) {
                    $studentSkills[] = $skill;
                }
            }
        }

        $matchedKeywords = array_values(array_intersect($jobRequiredSkills, $studentSkills));
        $missingKeywords = array_values(array_diff($jobRequiredSkills, $studentSkills));

        $matchedCount = count($matchedKeywords);
        
        // Exact 5-keyword scoring scale mandate:
        // 0/5 = 0%, 1/5 = 20%, 2/5 = 40%, 3/5 = 60%, 4/5 = 80%, 5/5 = 100%
        $overallScore = min(100, round(($matchedCount / 5) * 100));

        if ($overallScore >= 75) {
            $verdict = "🔥 High Shortlist Chance";
            $badgeBg = "#dcfce7";
            $badgeColor = "#15803d";
            $message = "Excellent match! Your skills, qualification, and uploaded resume closely align with this job drive.";
        } else if ($overallScore >= 50) {
            $verdict = "⚡ Moderate Shortlist Chance";
            $badgeBg = "#fef3c7";
            $badgeColor = "#b45309";
            $message = "Good foundation, but adding missing key technical skills will significantly increase your selection probability.";
        } else {
            $verdict = "⚠️ Low Shortlist Chance";
            $badgeBg = "#fee2e2";
            $badgeColor = "#b91c1c";
            $message = "Your profile currently has key skill gaps compared to this drive. Update your skills and profile summary to improve your score.";
        }

        $recommendations = [];
        if (empty($userSkills)) {
            $recommendations[] = "✏️ <strong>ADD SKILLS:</strong> Your profile has NO skills listed! Add relevant programming languages and tools (e.g. Python, Java, SQL, HTML).";
        }
        if (!empty($missingKeywords)) {
            $topMissing = array_slice($missingKeywords, 0, 4);
            $recommendations[] = "🎯 <strong>ADD KEY SKILLS:</strong> The job drive requires: <strong>" . implode(', ', array_map('ucwords', $topMissing)) . "</strong>.";
        }
        if (strlen($userAbout) < 20) {
            $recommendations[] = "📝 <strong>ABOUT ME:</strong> Expand your profile summary with your projects, certifications, and technical experience.";
        }
        if (empty($recommendations)) {
            $recommendations[] = "Your profile is well-optimized for this drive!";
        }

        return [
            'has_resume'        => true,
            'is_gemini'         => false,
            'score'             => $overallScore,
            'verdict'           => $verdict,
            'badge_bg'          => $badgeBg,
            'badge_color'       => $badgeColor,
            'message'           => $message,
            'matched_keywords'  => array_map('ucwords', $matchedKeywords),
            'missing_keywords'  => array_map('ucwords', array_slice($missingKeywords, 0, 6)),
            'recommendations'   => $recommendations
        ];
    }

    /**
     * Render AI Shortlist Match Widget HTML for UI integration
     */
    function renderAIMatchCard($analysis) {
        $hasResume = $analysis['has_resume'] ?? false;
        $isGemini  = $analysis['is_gemini'] ?? false;

        // ── 1. If NO RESUME uploaded -> Prompt User to Upload Resume to check eligibility ──
        if (!$hasResume) {
            $uploadUrl = file_exists('user/edit-profile.php') ? 'user/edit-profile.php' : 'edit-profile.php';

            return "
            <div style='background:linear-gradient(135deg, #fff5f5, #fff);border:2px dashed #fca5a5;border-radius:16px;padding:24px;margin-bottom:28px;box-shadow:0 8px 24px rgba(239,68,68,0.06);'>
                <div style='display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:12px;'>
                    <div style='display:flex;align-items:center;gap:10px;'>
                        <span style='font-size:28px;'>📄</span>
                        <h4 style='margin:0;font-family:\"Outfit\",sans-serif;font-weight:700;color:#991b1b;font-size:18px;'>
                            Resume Required for AI Eligibility Check
                        </h4>
                    </div>
                    <span style='background:#fee2e2;color:#991b1b;font-weight:700;font-size:13px;padding:6px 14px;border-radius:50px;'>
                        ⚠️ Action Required
                    </span>
                </div>

                <p style='font-size:14px;color:#7f1d1d;line-height:1.6;margin-bottom:18px;'>
                    You have not uploaded a PDF resume to your profile yet! Upload your resume to unlock your <strong>AI Shortlisting Probability Score</strong>, keyword analysis, and personalized recommendations for this job drive.
                </p>

                <a href='{$uploadUrl}' class='btn' style='background:#dc2626;color:#ffffff;font-weight:700;padding:10px 22px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(220,38,38,0.25);transition:0.2s;'>
                    <i class='fa fa-upload'></i> Add Resume to Check Eligibility
                </a>
            </div>
            ";
        }

        // ── 2. If RESUME IS UPLOADED -> Render Full AI Score Card ──
        $score = $analysis['score'];
        $verdict = $analysis['verdict'];
        $bg = $analysis['badge_bg'];
        $color = $analysis['badge_color'];
        $msg = $analysis['message'];

        // Determine Readiness Status & Advice
        if ($score >= 75) {
            $readinessBadge = "🚀 Ready to Apply!";
            $readinessBg = "#dcfce7";
            $readinessColor = "#15803d";
            $readinessAdvice = "<strong>✅ Ready to Submit:</strong> Your profile and resume are strong matches for this company drive. Click <strong>Apply Now</strong> below!";
        } else if ($score >= 50) {
            $readinessBadge = "⚡ Moderate Fit - Optional Updates";
            $readinessBg = "#fef3c7";
            $readinessColor = "#b45309";
            $readinessAdvice = "<strong>⚠️ Good to Apply with Caution:</strong> You satisfy minimum drive requirements, but adding the missing skills below to your profile will increase your shortlist chances.";
        } else {
            $readinessBadge = "🛑 Update Resume Before Applying";
            $readinessBg = "#fee2e2";
            $readinessColor = "#b91c1c";
            $readinessAdvice = "<strong>⛔ Hold On:</strong> Your profile currently has significant skill gaps for this role. We strongly recommend updating your skills & resume before submitting your application.";
        }

        $matched = !empty($analysis['matched_keywords']) ? implode(', ', $analysis['matched_keywords']) : 'None detected';
        $missing = !empty($analysis['missing_keywords']) ? implode(', ', $analysis['missing_keywords']) : 'None';

        $recsHtml = '';
        foreach ($analysis['recommendations'] as $rec) {
            $recsHtml .= "<li style='margin-bottom:6px;'>$rec</li>";
        }

        // Engine indicator pill
        $engineTag = $isGemini 
            ? "<span style='background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:50px;letter-spacing:.04em;'>⚡ Google Gemini AI</span>"
            : "<span style='background:#e2e8f0;color:#475569;font-size:11px;font-weight:700;padding:3px 10px;border-radius:50px;'>⚙️ Built-in Matcher</span>";

        $byokSettingsBtn = "";
        if (!$isGemini) {
            $byokUrl = file_exists('admin/ai-settings.php') ? 'admin/ai-settings.php' : (file_exists('ai-settings.php') ? 'ai-settings.php' : '../admin/ai-settings.php');
            $byokSettingsBtn = "
            <div style='margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;'>
                <small style='color:#64748b;'>Want real LLM shortlisting? Add your free Google AI Studio API Key.</small>
                <a href='{$byokUrl}' style='font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none;'>
                    🔑 Configure Gemini API Key (BYOK) &rarr;
                </a>
            </div>";
        }

        $matchedCount = !empty($analysis['matched_keywords']) ? count($analysis['matched_keywords']) : 0;
        $matchedCount = min(5, $matchedCount);

        return "
        <div style='background:var(--card-bg, #ffffff);border:1px solid var(--border-color, #e2e8f0);border-radius:16px;padding:24px;margin-bottom:28px;box-shadow:0 10px 30px rgba(79,70,229,0.08);'>
            <div style='display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px;'>
                <div style='display:flex;align-items:center;gap:10px;'>
                    <span style='font-size:26px;'>🤖</span>
                    <div>
                        <h4 style='margin:0;font-family:\"Outfit\",sans-serif;font-weight:700;color:var(--text-primary,#0f172a);font-size:18px;'>
                            AI Resume Eligibility & Readiness Analyzer
                        </h4>
                        <div style='margin-top:2px;'>{$engineTag}</div>
                    </div>
                </div>
                <span style='background:{$bg};color:{$color};font-weight:700;font-size:14px;padding:6px 14px;border-radius:50px;display:inline-block;'>
                    {$verdict}
                </span>
            </div>

            <!-- Readiness Status Callout -->
            <div style='background:{$readinessBg};color:{$readinessColor};border-left:4px solid {$readinessColor};padding:12px 16px;border-radius:8px;font-size:14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;'>
                <div>{$readinessAdvice}</div>
                <span style='font-weight:800;font-size:13px;text-transform:uppercase;letter-spacing:.04em;'>{$readinessBadge}</span>
            </div>

            <!-- Progress Bar -->
            <div style='margin-bottom:16px;'>
                <div style='display:flex;justify-content:space-between;font-size:13px;font-weight:600;color:var(--text-secondary,#475569);margin-bottom:6px;'>
                    <span>Selection Compatibility Score (<strong style='color:#4f46e5;'>{$matchedCount} / 5 Keywords Matched</strong>)</span>
                    <span style='color:{$color};font-weight:800;'>{$score}% Match</span>
                </div>
                <div style='height:10px;background:#e2e8f0;border-radius:50px;overflow:hidden;'>
                    <div style='width:{$score}%;height:100%;background:linear-gradient(90deg, #4f46e5, #7c3aed);border-radius:50px;transition:width 1s ease;'></div>
                </div>
            </div>

            <p style='font-size:14px;color:var(--text-secondary,#475569);line-height:1.5;margin-bottom:16px;'>
                {$msg}
            </p>

            <!-- Keyword Breakdown -->
            <div style='display:grid;grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));gap:16px;background:var(--bg-secondary, #f8fafc);padding:16px;border-radius:12px;margin-bottom:16px;'>
                <div>
                    <strong style='font-size:12px;text-transform:uppercase;color:#059652;letter-spacing:.05em;'>✓ Matched Resume Keywords</strong>
                    <div style='font-size:13px;font-weight:600;color:var(--text-primary,#0f172a);margin-top:4px;'>{$matched}</div>
                </div>
                <div>
                    <strong style='font-size:12px;text-transform:uppercase;color:#dc2626;letter-spacing:.05em;'>⚡ Missing Drive Skills / Gaps</strong>
                    <div style='font-size:13px;font-weight:600;color:var(--text-primary,#0f172a);margin-top:4px;'>{$missing}</div>
                </div>
            </div>

            <!-- AI Recommendations -->
            <div style='font-size:13px;color:var(--text-secondary,#475569);'>
                <strong style='color:var(--text-primary,#0f172a);'>💡 Suggestions to Maximize Selection Probability:</strong>
                <ul style='margin-top:6px;padding-left:18px;margin-bottom:0;'>
                    {$recsHtml}
                </ul>
            </div>

            {$byokSettingsBtn}
        </div>
        ";
    }

    /**
     * General ATS Resume Review & Optimization Engine
     */
    function evaluateGeneralATSResume($userProfile) {
        $apiKey = getGeminiAPIKey();
        if (!empty($apiKey)) {
            $modelsToTry = ['gemini-3.5-flash', 'gemini-flash-latest', 'gemini-3.5-flash-lite'];
            $userName   = ($userProfile['firstname'] ?? 'Student') . ' ' . ($userProfile['lastname'] ?? '');
            $userSkills = $userProfile['skills'] ?? 'Not specified';
            $userAbout  = $userProfile['aboutme'] ?? 'Not specified';
            $userQual   = $userProfile['qualification'] ?? '';
            $userStream = $userProfile['stream'] ?? '';
            $userHsc    = $userProfile['hsc'] ?? '';
            $userSsc    = $userProfile['ssc'] ?? '';
            $userUg     = $userProfile['ug'] ?? '';
            $userPg     = $userProfile['pg'] ?? '';
            $userResume = $userProfile['resume'] ?? '';

            $prompt = "You are an expert ATS (Applicant Tracking System) Specialist and AI Recruiter for campus placements. Evaluate the candidate's profile and resume details for general ATS compliance, readability, skill density, and recruiter shortlisting suitability.

CANDIDATE DETAILS:
- Name: {$userName}
- Qualification: {$userQual} ({$userStream})
- Academic Scores: HSC: {$userHsc}%, SSC: {$userSsc}%, UG: {$userUg}%, PG: {$userPg}%
- Technical Skills Listed: {$userSkills}
- Profile Summary / Bio: {$userAbout}
- PDF Resume Uploaded: " . (!empty($userResume) ? "Yes ({$userResume})" : "No") . "

Evaluate the resume across ATS standards and respond ONLY with a raw JSON object (no markdown, no code fences):
{
  \"ats_score\": (integer 0 to 100),
  \"verdict\": \"(e.g., 🌟 ATS Optimized / ⚡ Good ATS Health / ⚠️ Action Required)\",
  \"badge_bg\": \"#dcfce7\",
  \"badge_color\": \"#15803d\",
  \"summary\": \"(2-3 sentences concise professional evaluation of ATS quality)\",
  \"strengths\": [\"Strength 1\", \"Strength 2\", \"Strength 3\"],
  \"flaws\": [\"Flaw or gap 1\", \"Flaw or gap 2\"],
  \"recommendations\": [\"Actionable tip 1 to boost ATS score\", \"Actionable tip 2\", \"Actionable tip 3\"]
}";

            $payload = [
                "contents" => [["parts" => [["text" => $prompt]]]],
                "generationConfig" => ["temperature" => 0.2, "responseMimeType" => "application/json"]
            ];
            $jsonData = json_encode($payload);

            foreach ($modelsToTry as $m) {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key=" . urlencode($apiKey);
                $options = [
                    "http" => [
                        "method" => "POST",
                        "header" => "Content-Type: application/json\r\nContent-Length: " . strlen($jsonData) . "\r\n",
                        "content" => $jsonData,
                        "timeout" => 12,
                        "ignore_errors" => true
                    ],
                    "ssl" => ["verify_peer" => false, "verify_peer_name" => false]
                ];
                $context = stream_context_create($options);
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
                if (isset($json['error'])) continue;
                $rawText = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $rawText = preg_replace('/^```json\s*/i', '', trim($rawText));
                $rawText = preg_replace('/^```\s*/i', '', $rawText);
                $rawText = preg_replace('/\s*```$/i', '', $rawText);
                $res = json_decode($rawText, true);
                if (is_array($res) && isset($res['ats_score'])) {
                    $res['is_gemini'] = true;
                    return $res;
                }
            }
        }

        // Local ATS Fallback calculation
        $score = 40;
        $strengths = [];
        $flaws = [];
        $recs = [];

        if (!empty($userProfile['resume'])) { $score += 20; $strengths[] = "PDF Resume File Uploaded"; }
        else { $flaws[] = "No PDF resume uploaded"; $recs[] = "Upload a PDF resume to pass recruiter ATS filters."; }

        if (!empty($userProfile['skills'])) { $score += 20; $strengths[] = "Technical Skills Section Filled"; }
        else { $flaws[] = "Skills section empty"; $recs[] = "Add technical skills (e.g. Python, SQL, Java) to boost ATS score."; }

        if (strlen($userProfile['aboutme'] ?? '') > 30) { $score += 10; $strengths[] = "Profile Summary Provided"; }
        else { $recs[] = "Write a comprehensive summary in 'About Me' showcasing your strengths."; }

        if (!empty($userProfile['qualification'])) { $score += 10; }

        return [
            'is_gemini' => false,
            'ats_score' => min(100, $score),
            'verdict' => ($score >= 75 ? '🌟 ATS Optimized' : ($score >= 50 ? '⚡ Good ATS Health' : '⚠️ Action Required')),
            'badge_bg' => ($score >= 75 ? '#dcfce7' : ($score >= 50 ? '#fef3c7' : '#fee2e2')),
            'badge_color' => ($score >= 75 ? '#15803d' : ($score >= 50 ? '#b45309' : '#b91c1c')),
            'summary' => "Evaluated based on profile completeness, skills density, and attached resume file.",
            'strengths' => $strengths,
            'flaws' => $flaws,
            'recommendations' => $recs
        ];
    }

    /**
     * AI Skill Gap Analysis & Learning Roadmap Engine
     */
    function evaluateSkillGap($userProfile, $targetRole = 'Software Developer') {
        $targetRole = trim($targetRole) ?: 'Software Developer';

        // 1. Try Google AI Studio Gemini API first
        $apiKey = getGeminiAPIKey();
        if (!empty($apiKey)) {
            $geminiRes = callGeminiSkillGapAI($apiKey, $userProfile, $targetRole);
            if (is_array($geminiRes) && !isset($geminiRes['error']) && isset($geminiRes['already_have'])) {
                $geminiRes['is_gemini'] = true;
                return $geminiRes;
            }
        }

        // 2. Built-in Skill Gap Analyzer Fallback Engine
        $roleCatalog = [
            'Software Developer' => [
                'skills' => ['Java', 'SQL', 'Git', 'Spring Boot', 'REST API', 'Docker', 'Data Structures & Algorithms', 'OOP'],
                'p1' => ['phase' => 'Phase 1: Core Fundamentals (Weeks 1-2)', 'title' => 'Master Java, OOP & SQL Foundations', 'skills' => ['Java', 'SQL', 'Git'], 'description' => 'Strengthen your core programming principles, write clean object-oriented Java code, optimize SQL queries, and manage code via Git version control.'],
                'p2' => ['phase' => 'Phase 2: Frameworks & APIs (Weeks 3-4)', 'title' => 'Spring Boot & RESTful Services', 'skills' => ['Spring Boot', 'REST API'], 'description' => 'Build robust backend microservices with Spring Boot, implement REST APIs, handle security, and connect to database layers using JPA/Hibernate.'],
                'p3' => ['phase' => 'Phase 3: DevOps & Containerization (Weeks 5-6)', 'title' => 'Docker & Production Deployment', 'skills' => ['Docker', 'Data Structures & Algorithms'], 'description' => 'Package applications into lightweight Docker containers, write unit tests, and solve algorithmic problem sets to ace technical coding rounds.']
            ],
            'Full Stack Developer' => [
                'skills' => ['JavaScript', 'React', 'Node.js', 'HTML/CSS', 'SQL/MongoDB', 'REST API', 'Git', 'Docker'],
                'p1' => ['phase' => 'Phase 1: Frontend Mastery (Weeks 1-2)', 'title' => 'Responsive UI & Modern React', 'skills' => ['HTML/CSS', 'JavaScript', 'React'], 'description' => 'Build dynamic web applications using React components, state management, and modern CSS frameworks.'],
                'p2' => ['phase' => 'Phase 2: Backend Architecture (Weeks 3-4)', 'title' => 'Node.js, Express & Database APIs', 'skills' => ['Node.js', 'REST API', 'SQL/MongoDB'], 'description' => 'Design RESTful API services, model relational and NoSQL databases, and implement secure authentication.'],
                'p3' => ['phase' => 'Phase 3: Full Stack Deployment (Weeks 5-6)', 'title' => 'Docker & Cloud Deployment', 'skills' => ['Git', 'Docker'], 'description' => 'Containerize full stack applications with Docker Compose and deploy them to cloud platforms.']
            ],
            'Data Scientist' => [
                'skills' => ['Python', 'SQL', 'Pandas/NumPy', 'Machine Learning', 'Data Visualization', 'Statistics', 'Power BI'],
                'p1' => ['phase' => 'Phase 1: Data Wrangling (Weeks 1-2)', 'title' => 'Python, Pandas & SQL Queries', 'skills' => ['Python', 'SQL', 'Pandas/NumPy'], 'description' => 'Clean, manipulate, and analyze complex datasets using Pandas, NumPy, and complex SQL joins.'],
                'p2' => ['phase' => 'Phase 2: Machine Learning (Weeks 3-4)', 'title' => 'Predictive Modeling & Scikit-Learn', 'skills' => ['Machine Learning', 'Statistics'], 'description' => 'Train classification, regression, and clustering ML algorithms using Scikit-Learn.'],
                'p3' => ['phase' => 'Phase 3: Visualization & BI (Weeks 5-6)', 'title' => 'Dashboards & Model Deployment', 'skills' => ['Data Visualization', 'Power BI'], 'description' => 'Create interactive business intelligence dashboards and publish ML model APIs.']
            ],
            'DevOps / Cloud Engineer' => [
                'skills' => ['Linux', 'Docker', 'Kubernetes', 'AWS', 'CI/CD', 'Python/Bash', 'Git'],
                'p1' => ['phase' => 'Phase 1: Shell & Containers (Weeks 1-2)', 'title' => 'Linux Administration & Docker', 'skills' => ['Linux', 'Docker', 'Git'], 'description' => 'Master Linux terminal commands, script automation in Bash, and build multi-stage Docker images.'],
                'p2' => ['phase' => 'Phase 2: Orchestration & Cloud (Weeks 3-4)', 'title' => 'Kubernetes & AWS Infrastructure', 'skills' => ['Kubernetes', 'AWS'], 'description' => 'Deploy container clusters on Kubernetes (EKS) and configure AWS VPC, EC2, and S3 resources.'],
                'p3' => ['phase' => 'Phase 3: CI/CD Pipelines (Weeks 5-6)', 'title' => 'Automated Delivery & Monitoring', 'skills' => ['CI/CD', 'Python/Bash'], 'description' => 'Set up automated GitHub Actions or Jenkins CI/CD pipelines to deploy code seamlessly.']
            ],
            'Frontend Developer' => [
                'skills' => ['JavaScript', 'React', 'HTML5/CSS3', 'TypeScript', 'Tailwind', 'REST API', 'Git'],
                'p1' => ['phase' => 'Phase 1: Core Web Standard (Weeks 1-2)', 'title' => 'HTML5, Modern CSS & ES6+ JS', 'skills' => ['HTML5/CSS3', 'JavaScript', 'Git'], 'description' => 'Master semantic HTML, CSS Flexbox/Grid, responsive design, and asynchronous JavaScript.'],
                'p2' => ['phase' => 'Phase 2: Modern SPA Framework (Weeks 3-4)', 'title' => 'React & State Management', 'skills' => ['React', 'Tailwind'], 'description' => 'Build single-page web applications with React Hooks, Context API, and Tailwind CSS styling.'],
                'p3' => ['phase' => 'Phase 3: Type Safety & APIs (Weeks 5-6)', 'title' => 'TypeScript & REST API Integration', 'skills' => ['TypeScript', 'REST API'], 'description' => 'Migrate React projects to TypeScript for type safety and integrate async RESTful endpoints.']
            ]
        ];

        // Default to Software Developer catalog if target role not matched exactly
        $matchingCatalogKey = 'Software Developer';
        foreach ($roleCatalog as $roleName => $data) {
            if (stripos($targetRole, $roleName) !== false || stripos($roleName, $targetRole) !== false) {
                $matchingCatalogKey = $roleName;
                break;
            }
        }
        $roleData = $roleCatalog[$matchingCatalogKey];
        $requiredSkills = $roleData['skills'];

        $userSkillsRaw = strtolower($userProfile['skills'] ?? '');
        $userAboutRaw  = strtolower($userProfile['aboutme'] ?? '');
        $userText      = "$userSkillsRaw $userAboutRaw";

        $alreadyHave   = [];
        $needToImprove = [];

        foreach ($requiredSkills as $skill) {
            $sLower = strtolower($skill);
            $parts = explode('/', $sLower);
            $found = false;
            foreach ($parts as $p) {
                $p = trim($p);
                if (!empty($p) && strpos($userText, $p) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $alreadyHave[] = $skill;
            } else {
                $needToImprove[] = $skill;
            }
        }

        if (empty($alreadyHave) && !empty($userSkillsRaw)) {
            $tokens = array_filter(array_map('trim', explode(',', $userProfile['skills'])));
            foreach ($tokens as $t) {
                if (!empty($t)) $alreadyHave[] = ucwords($t);
            }
            $alreadyHave = array_slice(array_unique($alreadyHave), 0, 3);
        }

        if (empty($alreadyHave)) {
            $alreadyHave = ['Basic Programming Fundamentals', 'General Computer Science'];
        }

        $matchPercent = round((count($alreadyHave) / count($requiredSkills)) * 100);
        $matchPercent = max(25, min(95, $matchPercent));

        if ($matchPercent >= 75) {
            $verdict = "🚀 High Role Readiness";
        } else if ($matchPercent >= 50) {
            $verdict = "⚡ Moderate Skill Gap";
        } else {
            $verdict = "🛑 Significant Skill Gap";
        }

        return [
            'is_gemini' => false,
            'target_role' => $targetRole,
            'match_percentage' => $matchPercent,
            'verdict' => $verdict,
            'already_have' => array_values(array_unique($alreadyHave)),
            'need_to_improve' => array_values(array_unique($needToImprove)),
            'summary' => "Analysis based on your profile skills vs typical industry benchmarks for a {$targetRole}. Up-skilling in missing key areas will dramatically boost your placement drive selection chances.",
            'roadmap' => [
                $roleData['p1'],
                $roleData['p2'],
                $roleData['p3']
            ],
            'recommended_certifications' => [
                "Professional {$targetRole} Certification",
                "Industry Cloud & System Architecture Accreditation"
            ],
            'recommended_projects' => [
                "Full Stack {$targetRole} Enterprise Application",
                "Microservice & API Automation Suite for {$targetRole}"
            ]
        ];
    }

    /**
     * AI Job Drive Recommendation Engine
     * Evaluates candidate: Skills, Degree, CGPA/academic marks, Projects, Experience, Preferred Role
     */
    function getAIJobRecommendations($userProfile, $conn, $preferredRoleFilter = '') {
        $sql = "SELECT j.*, c.companyname, c.logo, c.city AS company_city, c.website 
                FROM job_post j 
                LEFT JOIN company c ON j.id_company = c.id_company 
                ORDER BY j.id_jobpost DESC";
        $res = $conn->query($sql);
        $drives = [];
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $drives[] = $row;
            }
        }

        if (empty($drives)) {
            return [];
        }

        $userSkills   = strtolower($userProfile['skills'] ?? '');
        $userQual     = strtoupper(trim($userProfile['qualification'] ?? 'MCA'));
        $userStream   = strtolower($userProfile['stream'] ?? '');
        $userAbout    = strtolower($userProfile['aboutme'] ?? '');
        $userUg       = floatval($userProfile['ug'] ?? 80);
        $userPg       = floatval($userProfile['pg'] ?? 80);
        $userScore    = max($userUg, $userPg);
        $prefRole     = strtolower(trim($preferredRoleFilter) ?: trim($userProfile['designation'] ?? ''));

        // 1. Try Google Gemini API if key is present
        $apiKey = getGeminiAPIKey();
        if (!empty($apiKey)) {
            $geminiResult = callGeminiJobRecommendationAI($apiKey, $userProfile, $drives);
            if (is_array($geminiResult) && !empty($geminiResult) && !isset($geminiResult['error'])) {
                $recMap = [];
                foreach ($geminiResult as $rec) {
                    if (isset($rec['id_jobpost'])) {
                        $recMap[$rec['id_jobpost']] = $rec;
                    }
                }
                $finalList = [];
                foreach ($drives as $d) {
                    $id = $d['id_jobpost'];
                    if (isset($recMap[$id])) {
                        $d['match_score']        = min(100, max(30, intval($recMap[$id]['match_score'] ?? 75)));
                        $d['verdict']            = $recMap[$id]['verdict'] ?? '⚡ Good Fit';
                        $d['match_reasons']      = $recMap[$id]['match_reasons'] ?? ['Matches your qualifications and profile.'];
                        $d['key_matching_skills']= $recMap[$id]['key_matching_skills'] ?? [];
                        $d['eligibility_status'] = $recMap[$id]['eligibility_status'] ?? 'Eligible';
                        $d['is_gemini']          = true;
                        $finalList[] = $d;
                    }
                }
                if (!empty($finalList)) {
                    usort($finalList, function($a, $b) { return $b['match_score'] <=> $a['match_score']; });
                    return $finalList;
                }
            }
        }

        // 2. Built-in Algorithmic Matcher Engine
        $techDictionary = [
            'python','java','javascript','c++','c#','php','sql','mysql','mongodb','postgresql',
            'react','angular','vue','nodejs','express','spring','boot','rest','api','docker','aws','git','oops','dsa'
        ];

        $studentSkillsList = [];
        foreach ($techDictionary as $sk) {
            if (strpos($userSkills, $sk) !== false || strpos($userAbout, $sk) !== false) {
                $studentSkillsList[] = $sk;
            }
        }

        $recommended = [];

        foreach ($drives as $drive) {
            $jTitle = strtolower($drive['jobtitle'] ?? '');
            $jDesc  = strtolower($drive['description'] ?? '');
            $jQual  = strtoupper(trim($drive['qualification'] ?? ''));
            $jCutoff= floatval($drive['maximumsalary'] ?? 60);

            $score = 50;
            $reasons = [];
            $matchedSkills = [];

            // A. Preferred Role Match (25 pts)
            if (!empty($prefRole) && (strpos($jTitle, $prefRole) !== false || strpos($prefRole, $jTitle) !== false)) {
                $score += 25;
                $reasons[] = "Direct match for your preferred role ('" . ucwords($prefRole) . "').";
            } else if (strpos($jTitle, 'software') !== false || strpos($jTitle, 'developer') !== false || strpos($jTitle, 'engineer') !== false) {
                $score += 15;
                $reasons[] = "Matches Software Engineering career path.";
            }

            // B. Degree / Qualification Match (25 pts)
            if (empty($jQual) || strpos($jQual, $userQual) !== false || strpos($userQual, $jQual) !== false) {
                $score += 25;
                $reasons[] = "Meets degree qualification requirement ({$userQual}).";
            } else {
                $score += 10;
            }

            // C. Academic Score / CGPA Cutoff (20 pts)
            if ($jCutoff > 0 && $userScore >= $jCutoff) {
                $score += 20;
                $reasons[] = "Your academic score ({$userScore}%) satisfies the company cutoff ({$jCutoff}%).";
            } else if ($userScore > 0) {
                $score += 10;
                $reasons[] = "Good academic standing ({$userScore}%).";
            }

            // D. Technical Skills & Projects Match (30 pts)
            foreach ($studentSkillsList as $sk) {
                if (strpos($jTitle, $sk) !== false || strpos($jDesc, $sk) !== false) {
                    $matchedSkills[] = ucwords($sk);
                }
            }
            if (!empty($matchedSkills)) {
                $matchedSkills = array_values(array_unique($matchedSkills));
                $score += min(30, count($matchedSkills) * 10);
                $reasons[] = "Matches key skills required by company: " . implode(', ', array_slice($matchedSkills, 0, 4)) . ".";
            } else {
                $reasons[] = "Profile skills align with general IT consulting & software development.";
            }

            $finalScore = min(98, max(40, $score));

            if ($finalScore >= 80) {
                $verdict = "🔥 Highly Recommended Drive";
            } else if ($finalScore >= 65) {
                $verdict = "⚡ Good Fit Drive";
            } else {
                $verdict = "⚠️ Moderate Fit Drive";
            }

            $drive['match_score']         = $finalScore;
            $drive['verdict']             = $verdict;
            $drive['match_reasons']       = array_slice($reasons, 0, 3);
            $drive['key_matching_skills'] = $matchedSkills;
            $drive['eligibility_status']  = ($userScore >= $jCutoff) ? "Eligible - Meets Academic Cutoff" : "Open for Application";
            $drive['is_gemini']           = false;

            $recommended[] = $drive;
        }

        usort($recommended, function($a, $b) {
            return $b['match_score'] <=> $a['match_score'];
        });

        return $recommended;
    }
}


