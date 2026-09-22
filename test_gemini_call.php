<?php
require_once("db.php");
require_once("php/gemini-api.php");

$apiKey = getGeminiAPIKey();
echo "Stored API Key: " . substr($apiKey, 0, 10) . "...\n\n";

$dummyUser = [
    'firstname' => 'Ravi',
    'lastname' => 'Kumar',
    'qualification' => 'MCA',
    'stream' => 'Computer Science',
    'skills' => 'Python, Java, MySQL, HTML, CSS, JavaScript',
    'aboutme' => 'Aspiring Full Stack Web Developer with experience in web applications and database design.'
];

$dummyJob = [
    'jobtitle' => 'Full Stack Developer',
    'qualification' => 'MCA',
    'experience' => 'Developer',
    'description' => 'We are seeking a Full Stack Developer proficient in Python, MySQL, JavaScript, HTML, and CSS. Knowledge of DSA and software design patterns is preferred.'
];

$res = callGeminiResumeAI($apiKey, $dummyUser, $dummyJob);

echo "--- GEMINI API RESPONSE ---\n";
print_r($res);
?>
