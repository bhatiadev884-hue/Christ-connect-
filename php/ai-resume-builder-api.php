<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

require_once('../db.php');
require_once('gemini-api.php');

$action = $_POST['action'] ?? $_GET['action'] ?? 'generate';

// Fetch user profile
$uid = (int)$_SESSION['id_user'];
$uQ  = $conn->query("SELECT * FROM users WHERE id_user='$uid'");
$user = $uQ ? $uQ->fetch_assoc() : [];

if ($action === 'generate_section') {
    $section   = $_POST['section']   ?? 'summary';
    $extraData = $_POST['extra']     ?? '';
    $targetRole= $_POST['target_role'] ?? 'Software Developer';

    $name     = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
    $skills   = $user['skills']   ?? '';
    $about    = $user['aboutme']  ?? '';
    $qual     = $user['qualification'] ?? '';
    $stream   = $user['stream']   ?? '';
    $ugMarks  = $user['ug']       ?? '';
    $hscMarks = $user['hsc']      ?? '';
    $city     = $user['city']     ?? '';

    $prompts = [
        'summary' => "You are a professional resume writer. Write a concise, powerful 3-4 sentence professional summary for a resume. 
Candidate: {$name}
Target Role: {$targetRole}
Degree: {$qual} in {$stream}
Skills: {$skills}
About: {$about}
City: {$city}
UG Marks: {$ugMarks}%

Write a polished, ATS-optimized professional summary in first person. Be specific, achievement-oriented, and highlight technical strengths. Output only the summary paragraph, nothing else.",

        'objective' => "Write a concise 2-3 sentence career objective for a resume.
Candidate: {$name}
Target Role: {$targetRole}
Degree: {$qual} in {$stream}
Skills: {$skills}

Write a compelling career objective in first person that shows ambition and alignment with the target role. Output only the objective paragraph, no extra text.",

        'skills_bullets' => "Convert these raw skills into a well-formatted, ATS-friendly list of 6-8 professional skill bullet points for a resume:
Skills: {$skills}
Target Role: {$targetRole}
Extra context: {$extraData}

Format as a simple JSON array of strings. Each item should be a concise, professional skill phrase. Example: [\"Proficient in Java and Spring Boot\", \"Experience with REST API development\"]. Output ONLY the JSON array.",

        'experience_bullets' => "Rewrite this work/internship experience into 3-4 professional resume bullet points using the STAR format (Situation, Task, Action, Result). 
Experience description: {$extraData}
Target Role: {$targetRole}
Skills context: {$skills}

Make each bullet start with a strong action verb. Quantify achievements where possible. Output as a JSON array of strings. Output ONLY the JSON array.",

        'project_description' => "Rewrite this project description into 2-3 professional resume bullet points:
Project info: {$extraData}
Skills: {$skills}
Target Role: {$targetRole}

Use strong action verbs, mention technologies, highlight impact. Output as a JSON array of strings. Output ONLY the JSON array.",

        'education_summary' => "Create a professional education summary line for resume:
Degree: {$qual} in {$stream}
UG Marks: {$ugMarks}
HSC Marks: {$hscMarks}
Extra: {$extraData}

Write 1-2 professional education bullet points. Output as a JSON array of strings. Output ONLY the JSON array.",
    ];

    $prompt = $prompts[$section] ?? $prompts['summary'];

    $apiKey = getGeminiAPIKey();
    if (empty($apiKey)) {
        // Fallback responses if no API key
        $fallbacks = [
            'summary' => "Results-driven {$qual} graduate in {$stream} with expertise in {$skills}. Passionate about leveraging technical skills to deliver innovative solutions. Seeking opportunities to contribute to organizational growth while continuously expanding my knowledge and capabilities.",
            'objective' => "To obtain a challenging position as {$targetRole} where I can apply my {$qual} background in {$stream} and hands-on skills in {$skills} to drive impactful results.",
            'skills_bullets' => json_encode(array_filter(array_map('trim', explode(',', $skills)))),
            'experience_bullets' => json_encode(["Contributed to team projects using {$skills}", "Collaborated cross-functionally to deliver solutions on time", "Developed and maintained technical documentation"]),
            'project_description' => json_encode(["Designed and implemented {$extraData} using {$skills}", "Delivered fully functional solution with measurable impact"]),
            'education_summary' => json_encode(["{$qual} in {$stream} | UG: {$ugMarks}% | HSC: {$hscMarks}%"]),
        ];
        echo json_encode(['success' => true, 'content' => $fallbacks[$section] ?? '', 'is_ai' => false]);
        exit();
    }

    $modelsToTry = ['gemini-1.5-flash', 'gemini-1.5-flash-latest', 'gemini-1.5-flash-8b'];
    $generatedContent = null;
    $lastError = '';

    foreach ($modelsToTry as $model) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        
        $payload = json_encode([
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 800]
        ]);

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 20,
                'ignore_errors' => true,
            ]
        ];
        $ctx = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $ctx);
        
        if ($raw === false) continue;
        $resp = json_decode($raw, true);
        
        $text = $resp['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($text) {
            // Clean up markdown code blocks if present
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/i', '', $text);
            $text = trim($text);
            $generatedContent = $text;
            break;
        }
        $lastError = $resp['error']['message'] ?? 'Unknown error';
    }

    if ($generatedContent !== null) {
        echo json_encode(['success' => true, 'content' => $generatedContent, 'is_ai' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $lastError ?: 'AI generation failed. Please check your API key.']);
    }
    exit();
}

if ($action === 'get_profile') {
    echo json_encode([
        'success' => true,
        'profile' => [
            'name'     => trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')),
            'email'    => $user['email']    ?? '',
            'phone'    => $user['contactno'] ?? '',
            'city'     => $user['city']     ?? '',
            'state'    => $user['state']    ?? '',
            'skills'   => $user['skills']   ?? '',
            'about'    => $user['aboutme']  ?? '',
            'qual'     => $user['qualification'] ?? '',
            'stream'   => $user['stream']   ?? '',
            'ug'       => $user['ug']       ?? '',
            'hsc'      => $user['hsc']      ?? '',
            'ssc'      => $user['ssc']      ?? '',
            'pg'       => $user['pg']       ?? '',
            'address'  => $user['address']  ?? '',
        ]
    ]);
    exit();
}

echo json_encode(['error' => 'Invalid action']);
