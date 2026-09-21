<?php
require_once("db.php");

echo "<style>body{font-family:'Inter',sans-serif;max-width:700px;margin:40px auto;padding:24px;background:#f4f6ff;}
h2{color:#4f46e5;font-family:'Outfit',sans-serif;}
.card{background:#fff;border-radius:14px;padding:24px;margin:16px 0;box-shadow:0 4px 20px rgba(79,70,229,.12);border-left:5px solid #4f46e5;}
.card.green{border-left-color:#059652;}
.card.violet{border-left-color:#7c3aed;}
code{background:#eef2ff;padding:3px 8px;border-radius:6px;font-size:14px;color:#4f46e5;}
.label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px;}
.val{font-size:16px;font-weight:600;color:#1e293b;margin-bottom:12px;}
.success{color:#059652;font-weight:700;font-size:13px;}
.error{color:#dc2626;font-weight:600;}
hr{border:none;border-top:1px solid #e2e8f0;margin:20px 0;}
</style>";

// Password encoder used by the app: base64_encode(strrev(md5($pass)))
function encodePass($pass) {
    return base64_encode(strrev(md5($pass)));
}

$errors = [];
$created = [];

// ─────────────────────────────────────────
// 1. SR ADMIN
// ─────────────────────────────────────────
$admin_user = 'sradmin';
$admin_pass = 'Admin@1234';

// Check if already exists
$check = $conn->query("SELECT id_admin FROM admin WHERE username='$admin_user'");
if ($check->num_rows == 0) {
    $sql = "INSERT INTO admin (username, password) VALUES ('$admin_user', '$admin_pass')";
    if ($conn->query($sql)) {
        $created[] = 'admin';
    } else {
        $errors[] = "Admin: " . $conn->error;
    }
} else {
    $created[] = 'admin_exists';
}

// ─────────────────────────────────────────
// 2. STUDENT
// ─────────────────────────────────────────
$s_email    = 'student@christccc.edu';
$s_password = encodePass('Student@1234');
$s_hash     = md5(time() . rand());

$checkS = $conn->query("SELECT id_user FROM users WHERE email='$s_email'");
if ($checkS->num_rows == 0) {
    $sql = "INSERT INTO users
        (firstname, lastname, email, password, address, city, state, contactno,
         qualification, stream, passingyear, dob, age, designation, resume, hash, aboutme, skills, active, hsc, ssc, ug, pg)
        VALUES
        ('Ravi', 'Kumar', '$s_email', '$s_password',
         '12 MCA Block, Christ University, NCR Campus', 'Delhi', 'Delhi', '9876543210',
         'MCA', 'Computer Science', '2025', '2001-06-15', '23',
         'Student', '', '$s_hash', 'Aspiring software developer with strong CS fundamentals.',
         'Python, Java, MySQL, HTML, CSS', '1', '85', '88', '80', '82')";
    if ($conn->query($sql)) {
        $created[] = 'student';
    } else {
        $errors[] = "Student: " . $conn->error;
    }
} else {
    $created[] = 'student_exists';
}

// ─────────────────────────────────────────
// 3. PLACEMENT CELL (Company)
// ─────────────────────────────────────────
$c_email    = 'placement@christccc.edu';
$c_password = encodePass('Placement@1234');

$checkC = $conn->query("SELECT id_company FROM company WHERE email='$c_email'");
if ($checkC->num_rows == 0) {
    $sql = "INSERT INTO company
        (name, companyname, country, state, city, contactno, website, email, password, aboutme, logo, active)
        VALUES
        ('Dr. Priya Sharma', 'Christ University Placement Cell', 'India', 'Delhi', 'Delhi',
         '9988776655', 'https://ncr.christuniversity.in/', '$c_email', '$c_password',
         'Official Placement Cell of Christ University NCR Campus. We connect students with top recruiters.',
         '', '1')";
    if ($conn->query($sql)) {
        $created[] = 'company';
    } else {
        $errors[] = "Placement Cell: " . $conn->error;
    }
} else {
    $created[] = 'company_exists';
}

$conn->close();

echo "<h2>🎓 Sample Account Setup</h2>";

if (!empty($errors)) {
    foreach ($errors as $e) {
        echo "<p class='error'>❌ Error — $e</p>";
    }
}
?>

<!-- Card 1: SR Admin -->
<div class="card">
    <h3 style="margin-top:0;color:#4f46e5;">🛡️ SR Admin</h3>
    <div class="label">Login URL</div>
    <div class="val"><a href="admin/index.php" style="color:#4f46e5;">http://localhost:8000/admin/index.php</a></div>
    <div class="label">Username</div>
    <div class="val"><code>sradmin</code></div>
    <div class="label">Password</div>
    <div class="val"><code>Admin@1234</code></div>
    <?php if (in_array('admin', $created)): ?>
        <span class="success">✅ Created successfully</span>
    <?php else: ?>
        <span style="color:#d97706;font-weight:600;font-size:13px;">⚠️ Already exists (not changed)</span>
    <?php endif; ?>
    <hr>
    <small style="color:#64748b;">Also, the <strong>original admin</strong> account still works: username <code>admin</code> / password <code>123456</code></small>
</div>

<!-- Card 2: Student -->
<div class="card green">
    <h3 style="margin-top:0;color:#059652;">🎓 Student</h3>
    <div class="label">Login URL</div>
    <div class="val"><a href="login-candidates.php" style="color:#059652;">http://localhost:8000/login-candidates.php</a></div>
    <div class="label">Email</div>
    <div class="val"><code>student@christccc.edu</code></div>
    <div class="label">Password</div>
    <div class="val"><code>Student@1234</code></div>
    <div class="label">Name</div>
    <div class="val">Ravi Kumar &nbsp;|&nbsp; MCA, Computer Science &nbsp;|&nbsp; Passing Year: 2025</div>
    <?php if (in_array('student', $created)): ?>
        <span class="success">✅ Created successfully</span>
    <?php else: ?>
        <span style="color:#d97706;font-weight:600;font-size:13px;">⚠️ Already exists (not changed)</span>
    <?php endif; ?>
</div>

<!-- Card 3: Placement Cell -->
<div class="card violet">
    <h3 style="margin-top:0;color:#7c3aed;">🏢 Placement Cell</h3>
    <div class="label">Login URL</div>
    <div class="val"><a href="login-company.php" style="color:#7c3aed;">http://localhost:8000/login-company.php</a></div>
    <div class="label">Email</div>
    <div class="val"><code>placement@christccc.edu</code></div>
    <div class="label">Password</div>
    <div class="val"><code>Placement@1234</code></div>
    <div class="label">Organization</div>
    <div class="val">Christ University Placement Cell &nbsp;|&nbsp; Dr. Priya Sharma</div>
    <?php if (in_array('company', $created)): ?>
        <span class="success">✅ Created successfully</span>
    <?php else: ?>
        <span style="color:#d97706;font-weight:600;font-size:13px;">⚠️ Already exists (not changed)</span>
    <?php endif; ?>
</div>

<p style="font-size:13px;color:#64748b;margin-top:24px;">
    ⚠️ You can delete <strong>setup_accounts.php</strong> from the server after setup is done for security.
</p>
