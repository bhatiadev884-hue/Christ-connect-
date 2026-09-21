<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Unauthorized session. Please log in.']);
    exit();
}

require_once(__DIR__ . "/../db.php");
require_once(__DIR__ . "/gemini-api.php");

// Fetch User Profile
$uQ = $conn->query("SELECT * FROM users WHERE id_user='$_SESSION[id_user]'");
if (!$uQ || $uQ->num_rows == 0) {
    echo json_encode(['error' => 'User profile not found.']);
    exit();
}
$userProfile = $uQ->fetch_assoc();

$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

$targetRole = trim($inputData['target_role'] ?? 'Java Developer');
$qaPairs    = $inputData['qa_pairs'] ?? [];

if (empty($qaPairs)) {
    echo json_encode(['error' => 'No interview answers provided for evaluation.']);
    exit();
}

// 1. Try Gemini API
$apiKey = getGeminiAPIKey();
if (!empty($apiKey)) {
    $geminiEval = callGeminiMockInterviewEvaluationAI($apiKey, $targetRole, $qaPairs, $userProfile);
    if (is_array($geminiEval) && !isset($geminiEval['error']) && isset($geminiEval['overall_score'])) {
        $geminiEval['status'] = 'success';
        $geminiEval['is_gemini'] = true;
        echo json_encode($geminiEval);
        exit();
    }
}

// 2. Local Fallback Mock Interview Evaluator
$totalQuestions = count($qaPairs);
$totalScoreAccum = 0;
$evaluations = [];
$strengths = [];
$improvements = [];

$techKeywords = [
    'java' => ['oop','class','object','inheritance','polymorphism','interface','abstract','exception','thread','stream','spring','hibernate','jpa','maven','garbage collection','jvm'],
    'software developer' => ['algorithm','data structure','array','hashmap','tree','git','sql','api','oops','testing','debug','architecture'],
    'full stack' => ['react','node','express','javascript','css','html','mongodb','sql','rest','git','state','api'],
    'python' => ['list','dict','tuple','pandas','numpy','dataframe','function','oop','django','flask','sql','exception']
];

$roleKey = 'java';
foreach (array_keys($techKeywords) as $k) {
    if (stripos($targetRole, $k) !== false) {
        $roleKey = $k;
        break;
    }
}
$expectedKeywords = $techKeywords[$roleKey] ?? $techKeywords['java'];

foreach ($qaPairs as $index => $item) {
    $qText = $item['question'] ?? "Question " . ($index + 1);
    $aText = trim($item['user_answer'] ?? '');
    $aLower = strtolower($aText);

    $wordCount = str_word_count($aText);
    $qScore = 40; // Base score
    $matched = [];

    if ($wordCount > 15) {
        $qScore += 20;
    } else if ($wordCount > 5) {
        $qScore += 10;
    }

    foreach ($expectedKeywords as $kw) {
        if (strpos($aLower, $kw) !== false) {
            $matched[] = $kw;
        }
    }

    if (!empty($matched)) {
        $qScore += min(40, count($matched) * 12);
    }

    $qScore = min(98, max(25, $qScore));
    $totalScoreAccum += $qScore;

    if ($qScore >= 80) {
        $feedback = "Excellent answer! Clear technical explanation using relevant terminology (" . implode(', ', array_slice($matched, 0, 3)) . ").";
    } else if ($qScore >= 60) {
        $feedback = "Good response covering core concepts, but expand on practical implementation details and framework usage.";
    } else {
        $feedback = "Brief response. Elaborate further with concrete coding examples, key principles, and architecture context.";
    }

    $evaluations[] = [
        'question' => $qText,
        'candidate_answer' => !empty($aText) ? $aText : "[No answer provided]",
        'score' => $qScore,
        'feedback' => $feedback,
        'ideal_answer' => "For a {$targetRole} role, explain the concept clearly, provide an example scenario or code structure, and mention time/space complexity or framework integrations."
    ];
}

$overallAvg = round($totalScoreAccum / max(1, $totalQuestions));
$techScore  = min(98, max(40, $overallAvg + 2));
$relScore   = min(98, max(45, $overallAvg - 2));
$commScore  = min(98, max(50, $overallAvg + 5));

if ($overallAvg >= 80) {
    $verdict = "🌟 Strong Hire - Outstanding Interview Performance";
    $strengths[] = "Strong technical vocabulary and clear domain concepts.";
    $strengths[] = "Structured, logical responses aligned with {$targetRole} requirements.";
    $improvements[] = "Keep practicing complex algorithm edge cases.";
} else if ($overallAvg >= 60) {
    $verdict = "⚡ Moderate Fit - Good Technical Foundation";
    $strengths[] = "Solid understanding of fundamental concepts.";
    $improvements[] = "Elaborate more on practical project examples during technical answers.";
    $improvements[] = "Include specific framework and tool references (e.g. Spring Boot, Docker, Git).";
} else {
    $verdict = "⚠️ Needs Further Preparation";
    $strengths[] = "Good enthusiasm and basic domain familiarity.";
    $improvements[] = "Provide more detailed, structured answers rather than short one-liners.";
    $improvements[] = "Review core data structures, OOP principles, and hands-on coding scenarios.";
}

echo json_encode([
    'status' => 'success',
    'is_gemini' => false,
    'overall_score' => $overallAvg,
    'verdict' => $verdict,
    'technical_score' => $techScore,
    'relevance_score' => $relScore,
    'communication_score' => $commScore,
    'summary' => "Simulated interview evaluation for a {$targetRole} position. Evaluated across technical accuracy, relevance, and communication clarity.",
    'strengths' => $strengths,
    'improvements' => $improvements,
    'question_evaluations' => $evaluations
]);
