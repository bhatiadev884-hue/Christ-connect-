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

// Fetch Active Drives for Context
$drivesQ = $conn->query("SELECT j.*, c.companyname FROM job_post j LEFT JOIN company c ON j.id_company = c.id_company ORDER BY j.id_jobpost DESC LIMIT 10");
$activeDrives = [];
if ($drivesQ && $drivesQ->num_rows > 0) {
    while ($d = $drivesQ->fetch_assoc()) {
        $activeDrives[] = $d;
    }
}

// Get user JSON payload
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);
$message = trim($inputData['message'] ?? $_POST['message'] ?? '');

if (empty($message)) {
    echo json_encode(['error' => 'Message content is empty.']);
    exit();
}

// 1. Check Gemini API
$apiKey = getGeminiAPIKey();
if (!empty($apiKey)) {
    $geminiRes = callGeminiCareerAssistantAI($apiKey, $userProfile, $message, $activeDrives);
    if (is_array($geminiRes) && !isset($geminiRes['error']) && isset($geminiRes['reply'])) {
        echo json_encode([
            'status' => 'success',
            'is_gemini' => true,
            'reply' => $geminiRes['reply'],
            'suggested_followups' => $geminiRes['suggested_followups'] ?? [
                "Am I eligible for active placement drives?",
                "What skills should I learn for a Java developer role?",
                "How can I improve my resume ATS score?",
                "What should I prepare for my technical interview?"
            ]
        ]);
        exit();
    }
}

// 2. Local Fallback Intelligent Assistant Engine
$msgLower = strtolower($message);
$userName = htmlspecialchars($userProfile['firstname'] ?? 'Student');
$userQual = htmlspecialchars($userProfile['qualification'] ?? 'MCA');
$userScore = max($userProfile['ug'] ?? 80, $userProfile['pg'] ?? 80);
$userSkills = htmlspecialchars($userProfile['skills'] ?? 'Not specified');

$replyHtml = "";
$followups = [
    "Am I eligible for active placement drives?",
    "What skills should I learn for a Java developer role?",
    "How can I improve my resume?",
    "What should I prepare for my technical interview?"
];

if (strpos($msgLower, 'eligible') !== false || strpos($msgLower, 'company') !== false || strpos($msgLower, 'drive') !== false) {
    $replyHtml = "Hello <strong>{$userName}</strong>! Based on your registered profile details:
    <ul>
        <li><strong>Degree / Qualification:</strong> {$userQual}</li>
        <li><strong>Academic CGPA / Score:</strong> {$userScore}%</li>
        <li><strong>Skills Listed:</strong> {$userSkills}</li>
    </ul>";
    
    if (!empty($activeDrives)) {
        $replyHtml .= "<p>Here is your live eligibility status for current campus placement drives:</p><ul>";
        foreach ($activeDrives as $d) {
            $cName = htmlspecialchars($d['companyname'] ?? 'Partner Company');
            $role = htmlspecialchars($d['jobtitle'] ?? 'Software Developer');
            $cutoff = floatval($d['maximumsalary'] ?? 60);
            $isEligible = ($userScore >= $cutoff);

            if ($isEligible) {
                $replyHtml .= "<li>✅ <strong>{$cName} ({$role}):</strong> <span style='color:#059652;font-weight:700;'>ELIGIBLE</span> (Your score {$userScore}% exceeds cutoff {$cutoff}%). <a href='view-job-post.php?id={$d['id_jobpost']}'>Apply Now &rarr;</a></li>";
            } else {
                $replyHtml .= "<li>⚠️ <strong>{$cName} ({$role}):</strong> Requires {$cutoff}% cutoff (Current: {$userScore}%). Update profile details if applicable.</li>";
            }
        }
        $replyHtml .= "</ul>";
    } else {
        $replyHtml .= "<p>You meet standard eligibility criteria for MCA/B.Tech drives requiring 60%+ marks!</p>";
    }

} else if (strpos($msgLower, 'java') !== false || strpos($msgLower, 'skills') !== false || strpos($msgLower, 'developer role') !== false) {
    $replyHtml = "Great question! To excel as a <strong>Java Developer</strong> for top recruiter drives (such as Accenture, Oracle, Cognizant), follow this structured skill roadmap:
    <ol>
        <li><strong>Core Java & OOP:</strong> Master Object-Oriented Programming principles, Exception Handling, Collections Framework, Multithreading, and Lambda Streams (Java 17+).</li>
        <li><strong>Database & SQL:</strong> Learn relational database design, complex SQL joins, index optimization, and JDBC / Spring Data JPA.</li>
        <li><strong>Spring Boot & REST APIs:</strong> Build RESTful microservices with Spring Boot, Spring Security, Dependency Injection, and Maven/Gradle.</li>
        <li><strong>DevOps & Testing:</strong> Containerize your APIs with <strong>Docker</strong>, write unit tests with <strong>JUnit / Mockito</strong>, and manage code via <strong>Git / GitHub</strong>.</li>
    </ol>
    <p>💡 <em>Pro Tip: Use our built-in <strong>AI Skill Gap Analysis</strong> tool in the portal menu to test your exact profile against Java Developer benchmarks!</em></p>";

} else if (strpos($msgLower, 'resume') !== false || strpos($msgLower, 'improve') !== false || strpos($msgLower, 'ats') !== false) {
    $replyHtml = "Here are 4 actionable recommendations to dramatically improve your resume for campus recruiters:
    <ol>
        <li><strong>Add Technical Keywords:</strong> Make sure your skills section contains explicit tech keywords like <code>Java</code>, <code>Python</code>, <code>SQL</code>, <code>REST API</code>, <code>Git</code>, and <code>Spring Boot</code>.</li>
        <li><strong>Quantify Project Achievements:</strong> Instead of just listing technologies, write outcome-driven bullets (e.g. <em>'Built a RESTful API reducing data retrieval time by 30%'</em>).</li>
        <li><strong>PDF File Upload:</strong> Always upload a clean single-column PDF resume without complex graphics or tables so ATS parsers can read it.</li>
        <li><strong>Complete Profile Summary:</strong> Expand your <em>'About Me'</em> bio with core project highlights and career aspirations.</li>
    </ol>
    <p>📄 You can check your live score anytime on the <a href='resume-review.php'>AI Resume ATS Review</a> page!</p>";

} else if (strpos($msgLower, 'interview') !== false || strpos($msgLower, 'technical') !== false || strpos($msgLower, 'prepare') !== false) {
    $replyHtml = "To clear technical interview rounds with confidence, prepare these core areas:
    <ul>
        <li><strong>Data Structures & Algorithms:</strong> Arrays, Strings, HashMaps, Linked Lists, Trees, and Sorting algorithms.</li>
        <li><strong>Core CS Fundamentals:</strong> OOPs concepts (Inheritance, Polymorphism, Encapsulation, Abstraction), Operating Systems, DBMS indexing, and Computer Networks.</li>
        <li><strong>System Design & Coding:</strong> Be ready to write clean code on a whiteboard or online editor and explain time/space complexity (Big-O notation).</li>
        <li><strong>Project Walkthrough:</strong> Be ready to explain your top project's architecture, database schema, and challenges solved.</li>
    </ul>
    <p>🎤 <em>Tip: You can now take a simulated interview right now using our new <strong>AI Mock Interview Simulator</strong> menu item!</em></p>";

} else {
    $replyHtml = "Hello <strong>{$userName}</strong>! I am your <strong>PlaceMentor AI Career Assistant</strong>. 🤖<br><br>
    I am here to help you navigate your campus placement journey! You can ask me anything about:
    <ul>
        <li>Company eligibility & academic cutoff criteria</li>
        <li>Skill roadmaps for target job roles (Java, Full Stack, Data Science)</li>
        <li>Resume ATS score optimization tips</li>
        <li>Technical & HR interview preparation strategies</li>
    </ul>
    Feel free to select one of the quick question chips below or type your question!";
}

echo json_encode([
    'status' => 'success',
    'is_gemini' => false,
    'reply' => $replyHtml,
    'suggested_followups' => $followups
]);
