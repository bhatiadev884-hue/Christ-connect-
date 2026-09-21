<?php
session_start();
if (empty($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit();
}
require_once("../db.php");

$uid = (int)$_SESSION['id_user'];
$uQ  = $conn->query("SELECT * FROM users WHERE id_user='$uid'");
$user = $uQ ? $uQ->fetch_assoc() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AI Resume Builder | PlaceMentor</title>
  <meta name="description" content="Build a professional, ATS-optimized resume with AI assistance. Auto-fill from your profile, generate polished content, and download instantly.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <style>
    :root {
      --primary: #4f46e5;
      --primary-dark: #3730a3;
      --secondary: #7c3aed;
      --accent: #06b6d4;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --dark: #0f172a;
      --card-bg: #ffffff;
      --body-bg: #f1f5f9;
      --border: #e2e8f0;
      --text-muted: #64748b;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--body-bg);
      color: var(--dark);
      min-height: 100vh;
    }

    /* ── TOP NAV ── */
    .top-nav {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      padding: 14px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 20px rgba(79,70,229,0.3);
      position: sticky;
      top: 0;
      z-index: 999;
    }
    .top-nav .brand {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .top-nav .brand .logo-icon {
      width: 36px; height: 36px;
      background: rgba(255,255,255,0.2);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .nav-actions { display: flex; gap: 10px; align-items: center; }
    .nav-btn {
      background: rgba(255,255,255,0.15);
      color: #fff;
      border: 1px solid rgba(255,255,255,0.3);
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s;
    }
    .nav-btn:hover { background: rgba(255,255,255,0.25); color: #fff; }
    .nav-btn.primary {
      background: #fff;
      color: var(--primary);
      border-color: #fff;
    }
    .nav-btn.primary:hover { background: #f0f0ff; }

    /* ── MAIN LAYOUT ── */
    .builder-layout {
      display: grid;
      grid-template-columns: 420px 1fr;
      gap: 0;
      min-height: calc(100vh - 64px);
    }

    /* ── EDITOR PANEL ── */
    .editor-panel {
      background: var(--card-bg);
      border-right: 1px solid var(--border);
      overflow-y: auto;
      max-height: calc(100vh - 64px);
      position: sticky;
      top: 64px;
    }
    .editor-header {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      padding: 20px 24px;
      border-bottom: 1px solid var(--border);
    }
    .editor-header h2 {
      font-size: 18px;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 4px;
    }
    .editor-header p {
      font-size: 13px;
      color: var(--text-muted);
    }

    /* Template Selector */
    .template-bar {
      padding: 16px 24px;
      border-bottom: 1px solid var(--border);
      background: #fafafa;
    }
    .template-bar label {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--text-muted);
      display: block;
      margin-bottom: 10px;
    }
    .template-thumbs {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .template-thumb {
      width: 56px; height: 72px;
      border-radius: 8px;
      border: 3px solid var(--border);
      cursor: pointer;
      transition: all 0.2s;
      overflow: hidden;
      position: relative;
      display: flex; flex-direction: column;
    }
    .template-thumb.active {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
    }
    .template-thumb:hover { transform: scale(1.05); }
    .template-thumb .t-top { height: 20px; }
    .template-thumb .t-lines { flex: 1; padding: 4px 6px; display: flex; flex-direction: column; gap: 3px; }
    .template-thumb .t-line { height: 3px; border-radius: 2px; background: #e2e8f0; }
    .template-thumb .t-name {
      position: absolute; bottom: -24px; left: 50%; transform: translateX(-50%);
      font-size: 10px; font-weight: 600; white-space: nowrap; color: var(--text-muted);
      transition: bottom 0.2s;
    }
    .template-thumb:hover .t-name, .template-thumb.active .t-name { bottom: -22px; color: var(--primary); }

    /* ── FORM SECTIONS ── */
    .editor-sections { padding: 0; }
    .section-accordion {
      border-bottom: 1px solid var(--border);
    }
    .section-toggle {
      width: 100%;
      padding: 16px 24px;
      background: none;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 14px;
      font-weight: 700;
      color: var(--dark);
      transition: background 0.2s;
      text-align: left;
    }
    .section-toggle:hover { background: #f8fafc; }
    .section-toggle .section-icon {
      width: 32px; height: 32px;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 14px;
      margin-right: 12px;
    }
    .section-toggle .left { display: flex; align-items: center; }
    .section-toggle .chevron {
      transition: transform 0.3s;
      color: var(--text-muted);
      font-size: 12px;
    }
    .section-toggle.open .chevron { transform: rotate(180deg); }
    .section-body {
      display: none;
      padding: 16px 24px 20px;
      background: #fff;
    }
    .section-body.open { display: block; }

    /* Form Elements */
    .form-label {
      font-size: 12px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 5px;
      display: block;
    }
    .form-control, .form-select {
      border: 1.5px solid var(--border);
      border-radius: 8px;
      padding: 9px 12px;
      font-size: 13px;
      color: var(--dark);
      transition: all 0.2s;
      width: 100%;
      margin-bottom: 12px;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
      outline: none;
    }
    textarea.form-control { resize: vertical; min-height: 80px; }

    /* AI Generate Button */
    .ai-gen-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      margin-bottom: 10px;
    }
    .ai-gen-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(79,70,229,0.3); }
    .ai-gen-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    .ai-gen-btn .spinner { display: none; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; }
    .ai-gen-btn.loading .spinner { display: inline-block; }
    .ai-gen-btn.loading .btn-text { display: none; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Add Item Button */
    .add-item-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f8fafc;
      border: 1.5px dashed var(--border);
      border-radius: 8px;
      padding: 10px 16px;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
      cursor: pointer;
      width: 100%;
      transition: all 0.2s;
      margin-top: 8px;
    }
    .add-item-btn:hover { border-color: var(--primary); color: var(--primary); background: #f0f0ff; }

    /* Entry Card */
    .entry-card {
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 14px;
      margin-bottom: 12px;
      position: relative;
    }
    .entry-card .remove-btn {
      position: absolute;
      top: 10px; right: 10px;
      background: none;
      border: none;
      color: var(--danger);
      cursor: pointer;
      font-size: 14px;
      opacity: 0.7;
      transition: opacity 0.2s;
    }
    .entry-card .remove-btn:hover { opacity: 1; }

    /* Color Picker */
    .color-swatches {
      display: flex; gap: 8px; margin-bottom: 12px;
    }
    .color-swatch {
      width: 28px; height: 28px;
      border-radius: 50%;
      cursor: pointer;
      border: 3px solid transparent;
      transition: all 0.2s;
    }
    .color-swatch.active {
      border-color: var(--dark);
      transform: scale(1.15);
    }

    /* ── PREVIEW PANEL ── */
    .preview-panel {
      background: #e8ecf1;
      padding: 24px;
      overflow-y: auto;
      max-height: calc(100vh - 64px);
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .preview-toolbar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      align-items: center;
      background: #fff;
      padding: 12px 16px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 800px;
    }
    .preview-toolbar .toolbar-label {
      font-size: 12px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-right: auto;
    }
    .toolbar-btn {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: 1.5px solid var(--border);
      background: #fff;
      color: var(--dark);
      transition: all 0.2s;
    }
    .toolbar-btn:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }
    .toolbar-btn.primary {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
    }
    .toolbar-btn.primary:hover { background: var(--primary-dark); }

    /* Resume Preview */
    #resume-preview-wrapper {
      width: 100%;
      max-width: 800px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.15);
      border-radius: 4px;
      overflow: hidden;
    }
    #resume-preview {
      background: #fff;
      width: 100%;
      min-height: 1100px;
      font-family: 'Inter', sans-serif;
    }

    /* ══════════════════════ RESUME TEMPLATES ══════════════════════ */

    /* Template 1: Classic Blue (default) */
    #resume-preview.tmpl-classic .resume-header {
      background: #1e3a5f;
      color: #fff;
      padding: 36px 40px 28px;
    }
    #resume-preview.tmpl-classic .resume-name { font-size: 32px; font-weight: 800; letter-spacing: -0.5px; }
    #resume-preview.tmpl-classic .resume-title { font-size: 15px; opacity: 0.85; margin-top: 4px; font-weight: 500; }
    #resume-preview.tmpl-classic .resume-contact {
      display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px;
      font-size: 12.5px; opacity: 0.9;
    }
    #resume-preview.tmpl-classic .resume-contact span { display: flex; align-items: center; gap: 5px; }
    #resume-preview.tmpl-classic .resume-body { display: grid; grid-template-columns: 1fr 2fr; }
    #resume-preview.tmpl-classic .sidebar {
      background: #f0f4f8;
      padding: 28px 24px;
    }
    #resume-preview.tmpl-classic .main-col { padding: 28px 32px; }
    #resume-preview.tmpl-classic .section-title {
      font-size: 11px; font-weight: 800; text-transform: uppercase;
      letter-spacing: 0.1em; color: #1e3a5f; border-bottom: 2px solid #1e3a5f;
      padding-bottom: 6px; margin-bottom: 12px;
    }
    #resume-preview.tmpl-classic .sidebar .section-title { color: #1e3a5f; border-bottom-color: #1e3a5f; }
    #resume-preview.tmpl-classic .section { margin-bottom: 22px; }
    #resume-preview.tmpl-classic .skill-tag {
      display: inline-block;
      background: #e1e8f0;
      color: #1e3a5f;
      border-radius: 4px;
      padding: 3px 9px;
      font-size: 11.5px;
      font-weight: 600;
      margin: 3px 3px 3px 0;
    }
    #resume-preview.tmpl-classic .exp-item { margin-bottom: 18px; }
    #resume-preview.tmpl-classic .exp-title { font-size: 14px; font-weight: 700; color: #1e3a5f; }
    #resume-preview.tmpl-classic .exp-sub { font-size: 12px; color: #64748b; margin-bottom: 6px; }
    #resume-preview.tmpl-classic .exp-bullets { margin: 0; padding-left: 18px; }
    #resume-preview.tmpl-classic .exp-bullets li { font-size: 12.5px; color: #374151; margin-bottom: 4px; line-height: 1.5; }
    #resume-preview.tmpl-classic .summary-text { font-size: 13px; color: #374151; line-height: 1.6; }
    #resume-preview.tmpl-classic .edu-item { margin-bottom: 14px; }
    #resume-preview.tmpl-classic .edu-degree { font-size: 13.5px; font-weight: 700; color: #1e3a5f; }
    #resume-preview.tmpl-classic .edu-detail { font-size: 12px; color: #64748b; }

    /* Template 2: Modern Purple */
    #resume-preview.tmpl-modern .resume-header {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: #fff;
      padding: 40px;
      display: flex;
      align-items: center;
      gap: 28px;
    }
    #resume-preview.tmpl-modern .avatar-circle {
      width: 80px; height: 80px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      border: 3px solid rgba(255,255,255,0.5);
      display: flex; align-items: center; justify-content: center;
      font-size: 32px; font-weight: 800; color: #fff;
      flex-shrink: 0;
    }
    #resume-preview.tmpl-modern .resume-name { font-size: 30px; font-weight: 800; }
    #resume-preview.tmpl-modern .resume-title { font-size: 14px; opacity: 0.85; margin-top: 4px; }
    #resume-preview.tmpl-modern .resume-contact { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; font-size: 12px; opacity: 0.9; }
    #resume-preview.tmpl-modern .resume-contact span { display: flex; align-items: center; gap: 5px; }
    #resume-preview.tmpl-modern .resume-body { display: grid; grid-template-columns: 240px 1fr; }
    #resume-preview.tmpl-modern .sidebar { background: #f8f7ff; padding: 28px 20px; border-right: 1px solid #e5e2ff; }
    #resume-preview.tmpl-modern .main-col { padding: 28px 32px; }
    #resume-preview.tmpl-modern .section-title {
      font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;
      color: #4f46e5; padding-bottom: 6px; border-bottom: 2px solid #4f46e5; margin-bottom: 12px;
    }
    #resume-preview.tmpl-modern .section { margin-bottom: 22px; }
    #resume-preview.tmpl-modern .skill-tag {
      display: inline-block;
      background: #ede9fe;
      color: #5b21b6;
      border-radius: 20px;
      padding: 3px 10px;
      font-size: 11.5px;
      font-weight: 600;
      margin: 3px 3px 3px 0;
    }
    #resume-preview.tmpl-modern .exp-item { margin-bottom: 18px; }
    #resume-preview.tmpl-modern .exp-title { font-size: 14px; font-weight: 700; color: #3730a3; }
    #resume-preview.tmpl-modern .exp-sub { font-size: 12px; color: #6b7280; margin-bottom: 6px; }
    #resume-preview.tmpl-modern .exp-bullets { margin: 0; padding-left: 18px; }
    #resume-preview.tmpl-modern .exp-bullets li { font-size: 12.5px; color: #374151; margin-bottom: 4px; line-height: 1.5; }
    #resume-preview.tmpl-modern .summary-text { font-size: 13px; color: #374151; line-height: 1.6; }
    #resume-preview.tmpl-modern .edu-item { margin-bottom: 14px; }
    #resume-preview.tmpl-modern .edu-degree { font-size: 13.5px; font-weight: 700; color: #4f46e5; }
    #resume-preview.tmpl-modern .edu-detail { font-size: 12px; color: #6b7280; }

    /* Template 3: Minimal Black */
    #resume-preview.tmpl-minimal .resume-header {
      padding: 40px 48px 24px;
      border-bottom: 3px solid #000;
    }
    #resume-preview.tmpl-minimal .resume-name {
      font-size: 36px; font-weight: 800; color: #000; letter-spacing: -1px;
      font-family: 'Merriweather', serif;
    }
    #resume-preview.tmpl-minimal .resume-title { font-size: 14px; color: #555; margin-top: 4px; }
    #resume-preview.tmpl-minimal .resume-contact { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 12px; font-size: 12px; color: #555; }
    #resume-preview.tmpl-minimal .resume-contact span { display: flex; align-items: center; gap: 5px; }
    #resume-preview.tmpl-minimal .resume-body { padding: 28px 48px; }
    #resume-preview.tmpl-minimal .section-title {
      font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em;
      color: #000; border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 14px;
    }
    #resume-preview.tmpl-minimal .section { margin-bottom: 24px; }
    #resume-preview.tmpl-minimal .skill-tag {
      display: inline-block;
      border: 1px solid #333;
      color: #333;
      border-radius: 3px;
      padding: 2px 8px;
      font-size: 11.5px;
      font-weight: 600;
      margin: 3px 3px 3px 0;
    }
    #resume-preview.tmpl-minimal .exp-item { margin-bottom: 18px; }
    #resume-preview.tmpl-minimal .exp-title { font-size: 14px; font-weight: 700; color: #000; }
    #resume-preview.tmpl-minimal .exp-sub { font-size: 12px; color: #666; margin-bottom: 6px; }
    #resume-preview.tmpl-minimal .exp-bullets { margin: 0; padding-left: 18px; }
    #resume-preview.tmpl-minimal .exp-bullets li { font-size: 12.5px; color: #333; margin-bottom: 4px; line-height: 1.5; }
    #resume-preview.tmpl-minimal .summary-text { font-size: 13px; color: #333; line-height: 1.7; }
    #resume-preview.tmpl-minimal .edu-item { margin-bottom: 14px; }
    #resume-preview.tmpl-minimal .edu-degree { font-size: 13.5px; font-weight: 700; color: #000; }
    #resume-preview.tmpl-minimal .edu-detail { font-size: 12px; color: #666; }

    /* Template 4: Teal Creative */
    #resume-preview.tmpl-creative .resume-header {
      background: #0f766e;
      color: #fff;
      padding: 36px 40px;
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 24px;
      align-items: center;
    }
    #resume-preview.tmpl-creative .header-right {
      text-align: right;
    }
    #resume-preview.tmpl-creative .resume-name { font-size: 32px; font-weight: 800; }
    #resume-preview.tmpl-creative .resume-title { font-size: 14px; opacity: 0.85; margin-top: 4px; background: rgba(0,0,0,0.15); display: inline-block; padding: 2px 10px; border-radius: 4px; }
    #resume-preview.tmpl-creative .resume-contact { display: flex; flex-direction: column; gap: 5px; font-size: 12px; opacity: 0.9; }
    #resume-preview.tmpl-creative .resume-contact span { display: flex; align-items: center; gap: 5px; justify-content: flex-end; }
    #resume-preview.tmpl-creative .resume-body { display: grid; grid-template-columns: 1fr 1fr; }
    #resume-preview.tmpl-creative .sidebar { padding: 28px 24px 28px 40px; border-right: 1px solid #e2e8f0; }
    #resume-preview.tmpl-creative .main-col { padding: 28px 40px 28px 24px; }
    #resume-preview.tmpl-creative .section-title {
      font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em;
      color: #0f766e; padding-bottom: 6px; border-bottom: 2px solid #0f766e; margin-bottom: 12px;
    }
    #resume-preview.tmpl-creative .section { margin-bottom: 22px; }
    #resume-preview.tmpl-creative .skill-tag {
      display: inline-block;
      background: #ccfbf1;
      color: #0f766e;
      border-radius: 4px;
      padding: 3px 9px;
      font-size: 11.5px;
      font-weight: 600;
      margin: 3px 3px 3px 0;
    }
    #resume-preview.tmpl-creative .exp-item { margin-bottom: 18px; }
    #resume-preview.tmpl-creative .exp-title { font-size: 14px; font-weight: 700; color: #0f766e; }
    #resume-preview.tmpl-creative .exp-sub { font-size: 12px; color: #64748b; margin-bottom: 6px; }
    #resume-preview.tmpl-creative .exp-bullets { margin: 0; padding-left: 18px; }
    #resume-preview.tmpl-creative .exp-bullets li { font-size: 12.5px; color: #374151; margin-bottom: 4px; line-height: 1.5; }
    #resume-preview.tmpl-creative .summary-text { font-size: 13px; color: #374151; line-height: 1.6; }
    #resume-preview.tmpl-creative .edu-item { margin-bottom: 14px; }
    #resume-preview.tmpl-creative .edu-degree { font-size: 13.5px; font-weight: 700; color: #0f766e; }
    #resume-preview.tmpl-creative .edu-detail { font-size: 12px; color: #64748b; }

    /* ── TOAST ── */
    .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
    .toast-msg {
      display: flex; align-items: center; gap: 10px;
      background: #1e293b;
      color: #fff;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      margin-top: 8px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      animation: slideIn 0.3s ease;
    }
    @keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .toast-msg.success { border-left: 4px solid var(--success); }
    .toast-msg.error   { border-left: 4px solid var(--danger); }
    .toast-msg.info    { border-left: 4px solid var(--accent); }

    /* Responsive */
    @media (max-width: 900px) {
      .builder-layout { grid-template-columns: 1fr; }
      .editor-panel { max-height: none; position: static; }
      .preview-panel { max-height: none; }
    }

    @media print {
      .top-nav, .editor-panel, .preview-toolbar { display: none !important; }
      .preview-panel { padding: 0; background: #fff; }
      #resume-preview-wrapper { box-shadow: none; }
    }
  </style>
</head>
<body>

<!-- Top Navigation -->
<nav class="top-nav">
  <a href="index.php" class="brand">
    <span class="logo-icon">🎯</span>
    PlaceMentor
  </a>
  <div class="nav-actions">
    <a href="resume-review.php" class="nav-btn"><i class="fas fa-star" style="margin-right:5px;"></i>ATS Review</a>
    <a href="index.php" class="nav-btn"><i class="fas fa-arrow-left" style="margin-right:5px;"></i>Dashboard</a>
    <button onclick="downloadPDF()" class="nav-btn primary"><i class="fas fa-download" style="margin-right:5px;"></i>Download PDF</button>
  </div>
</nav>

<div class="builder-layout">

  <!-- ── EDITOR PANEL ── -->
  <div class="editor-panel">
    <div class="editor-header">
      <h2>✨ AI Resume Builder</h2>
      <p>Fill in your details below — use AI to generate polished content</p>
    </div>

    <!-- Template Selector -->
    <div class="template-bar">
      <label>Choose Template</label>
      <div class="template-thumbs">
        <div class="template-thumb active" data-tmpl="tmpl-classic" title="Classic Blue" onclick="selectTemplate(this)">
          <div class="t-top" style="background:#1e3a5f;"></div>
          <div class="t-lines"><div class="t-line"></div><div class="t-line" style="width:70%;"></div><div class="t-line"></div></div>
        </div>
        <div class="template-thumb" data-tmpl="tmpl-modern" title="Modern Purple" onclick="selectTemplate(this)">
          <div class="t-top" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);"></div>
          <div class="t-lines"><div class="t-line"></div><div class="t-line" style="width:80%;"></div><div class="t-line"></div></div>
        </div>
        <div class="template-thumb" data-tmpl="tmpl-minimal" title="Minimal" onclick="selectTemplate(this)">
          <div class="t-top" style="background:#000;height:4px;"></div>
          <div class="t-lines"><div class="t-line" style="background:#000;"></div><div class="t-line" style="width:60%;background:#999;"></div><div class="t-line"></div></div>
        </div>
        <div class="template-thumb" data-tmpl="tmpl-creative" title="Teal" onclick="selectTemplate(this)">
          <div class="t-top" style="background:#0f766e;"></div>
          <div class="t-lines"><div class="t-line"></div><div class="t-line" style="width:75%;"></div><div class="t-line"></div></div>
        </div>
      </div>
    </div>

    <div class="editor-sections">

      <!-- PERSONAL INFO -->
      <div class="section-accordion">
        <button class="section-toggle open" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#eff6ff;color:#3b82f6;">👤</span>
            Personal Information
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body open">
          <label class="form-label">Full Name</label>
          <input type="text" class="form-control" id="f-name" placeholder="Your Full Name">

          <label class="form-label">Target Job Title</label>
          <input type="text" class="form-control" id="f-title" placeholder="e.g. Software Developer, Data Analyst">

          <label class="form-label">Email</label>
          <input type="text" class="form-control" id="f-email" placeholder="your@email.com">

          <label class="form-label">Phone</label>
          <input type="text" class="form-control" id="f-phone" placeholder="+91 XXXXX XXXXX">

          <label class="form-label">Location</label>
          <input type="text" class="form-control" id="f-location" placeholder="City, State">

          <label class="form-label">LinkedIn / GitHub / Portfolio (optional)</label>
          <input type="text" class="form-control" id="f-links" placeholder="linkedin.com/in/yourprofile">
        </div>
      </div>

      <!-- PROFESSIONAL SUMMARY -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#f0fdf4;color:#16a34a;">📝</span>
            Professional Summary
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <button class="ai-gen-btn" id="ai-summary-btn" onclick="generateWithAI('summary', 'f-summary', 'ai-summary-btn')">
            <span class="spinner"></span>
            <span class="btn-text">✨ Generate with AI</span>
          </button>
          <textarea class="form-control" id="f-summary" rows="5" placeholder="A brief professional summary highlighting your key strengths, experience, and career goals..."></textarea>
        </div>
      </div>

      <!-- SKILLS -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#fef9c3;color:#ca8a04;">⚡</span>
            Skills
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <button class="ai-gen-btn" id="ai-skills-btn" onclick="generateWithAI('skills_bullets', 'f-skills', 'ai-skills-btn')">
            <span class="spinner"></span>
            <span class="btn-text">✨ AI-Polish My Skills</span>
          </button>
          <label class="form-label">Skills (comma-separated or one per line)</label>
          <textarea class="form-control" id="f-skills" rows="4" placeholder="Java, Python, React, SQL, Machine Learning..."></textarea>
        </div>
      </div>

      <!-- EDUCATION -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#fff7ed;color:#ea580c;">🎓</span>
            Education
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <div id="edu-list"></div>
          <button class="add-item-btn" onclick="addEducation()">
            <i class="fas fa-plus"></i> Add Education
          </button>
        </div>
      </div>

      <!-- EXPERIENCE -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#fdf4ff;color:#9333ea;">💼</span>
            Work Experience / Internship
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <div id="exp-list"></div>
          <button class="add-item-btn" onclick="addExperience()">
            <i class="fas fa-plus"></i> Add Experience / Internship
          </button>
        </div>
      </div>

      <!-- PROJECTS -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#ecfdf5;color:#059669;">🚀</span>
            Projects
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <div id="proj-list"></div>
          <button class="add-item-btn" onclick="addProject()">
            <i class="fas fa-plus"></i> Add Project
          </button>
        </div>
      </div>

      <!-- CERTIFICATIONS -->
      <div class="section-accordion">
        <button class="section-toggle" onclick="toggleSection(this)">
          <span class="left">
            <span class="section-icon" style="background:#f0f9ff;color:#0284c7;">🏅</span>
            Certifications & Achievements
          </span>
          <i class="fas fa-chevron-down chevron"></i>
        </button>
        <div class="section-body">
          <label class="form-label">List your certifications, awards, or achievements (one per line)</label>
          <textarea class="form-control" id="f-certs" rows="4" placeholder="AWS Certified Developer&#10;Google Cloud Associate&#10;Winner, HackathonXYZ 2024"></textarea>
        </div>
      </div>

    </div><!-- /editor-sections -->
  </div><!-- /editor-panel -->

  <!-- ── PREVIEW PANEL ── -->
  <div class="preview-panel">
    <div class="preview-toolbar">
      <span class="toolbar-label">📄 Live Preview</span>
      <button class="toolbar-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
      <button class="toolbar-btn primary" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Download PDF</button>
    </div>

    <div id="resume-preview-wrapper">
      <div id="resume-preview" class="tmpl-classic">
        <!-- Rendered by JavaScript -->
      </div>
    </div>
  </div>

</div><!-- /builder-layout -->

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
// ── STATE ── 
const state = {
  template: 'tmpl-classic',
  personal: { name: '', title: '', email: '', phone: '', location: '', links: '' },
  summary: '',
  skills: '',
  education: [],
  experience: [],
  projects: [],
  certs: ''
};

// PHP-injected user profile
const userProfile = {
  name:     <?= json_encode(trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''))) ?>,
  email:    <?= json_encode($user['email']    ?? '') ?>,
  phone:    <?= json_encode($user['contactno'] ?? '') ?>,
  city:     <?= json_encode(($user['city'] ?? '') . (($user['state'] ?? '') ? ', ' . ($user['state'] ?? '') : '')) ?>,
  skills:   <?= json_encode($user['skills']   ?? '') ?>,
  about:    <?= json_encode($user['aboutme']  ?? '') ?>,
  qual:     <?= json_encode($user['qualification'] ?? '') ?>,
  stream:   <?= json_encode($user['stream']   ?? '') ?>,
  ug:       <?= json_encode($user['ug']       ?? '') ?>,
  hsc:      <?= json_encode($user['hsc']      ?? '') ?>,
  ssc:      <?= json_encode($user['ssc']      ?? '') ?>,
};

// ── INIT ──
document.addEventListener('DOMContentLoaded', () => {
  // Auto-fill from profile
  setValue('f-name',     userProfile.name);
  setValue('f-email',    userProfile.email);
  setValue('f-phone',    userProfile.phone);
  setValue('f-location', userProfile.city);
  setValue('f-skills',   userProfile.skills);
  setValue('f-summary',  userProfile.about);

  // Add default education from profile
  if (userProfile.qual) {
    addEducation({
      degree: userProfile.qual + (userProfile.stream ? ' in ' + userProfile.stream : ''),
      school: '',
      year: '',
      marks: userProfile.ug ? 'CGPA/Marks: ' + userProfile.ug : ''
    });
  }
  if (userProfile.hsc) {
    addEducation({ degree: '12th (HSC)', school: '', year: '', marks: 'Marks: ' + userProfile.hsc + '%' });
  }
  if (userProfile.ssc) {
    addEducation({ degree: '10th (SSC)', school: '', year: '', marks: 'Marks: ' + userProfile.ssc + '%' });
  }

  // Attach live-update listeners
  document.querySelectorAll('[id^="f-"]').forEach(el => {
    el.addEventListener('input', updatePreview);
  });

  updatePreview();
});

function setValue(id, val) {
  const el = document.getElementById(id);
  if (el && val) el.value = val;
}

// ── TEMPLATE SELECTOR ──
function selectTemplate(el) {
  document.querySelectorAll('.template-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  state.template = el.dataset.tmpl;
  updatePreview();
}

// ── SECTION TOGGLE ──
function toggleSection(btn) {
  btn.classList.toggle('open');
  const body = btn.nextElementSibling;
  body.classList.toggle('open');
}

// ── ADD EDUCATION ──
let eduCount = 0;
function addEducation(prefill = {}) {
  eduCount++;
  const id = 'edu-' + eduCount;
  const div = document.createElement('div');
  div.className = 'entry-card';
  div.id = id;
  div.innerHTML = `
    <button class="remove-btn" onclick="removeEntry('${id}')"><i class="fas fa-times"></i></button>
    <input type="text" class="form-control edu-degree" placeholder="Degree / Certification" value="${escapeAttr(prefill.degree || '')}">
    <input type="text" class="form-control edu-school" placeholder="School / University Name">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
      <input type="text" class="form-control edu-year" placeholder="Year (e.g. 2024)">
      <input type="text" class="form-control edu-marks" placeholder="CGPA / Percentage" value="${escapeAttr(prefill.marks || '')}">
    </div>
  `;
  div.querySelectorAll('input').forEach(el => el.addEventListener('input', updatePreview));
  document.getElementById('edu-list').appendChild(div);
  if (prefill.school) div.querySelector('.edu-school').value = prefill.school;
  if (prefill.year)   div.querySelector('.edu-year').value   = prefill.year;
  updatePreview();
}

// ── ADD EXPERIENCE ──
let expCount = 0;
function addExperience() {
  expCount++;
  const id = 'exp-' + expCount;
  const div = document.createElement('div');
  div.className = 'entry-card';
  div.id = id;
  div.innerHTML = `
    <button class="remove-btn" onclick="removeEntry('${id}')"><i class="fas fa-times"></i></button>
    <input type="text" class="form-control exp-title" placeholder="Job Title / Role">
    <input type="text" class="form-control exp-company" placeholder="Company / Organization">
    <input type="text" class="form-control exp-period" placeholder="Duration (e.g. Jun 2023 – Aug 2023)">
    <button class="ai-gen-btn" style="width:100%;justify-content:center;" onclick="generateExpBullets(this, '${id}')">
      <span class="spinner"></span><span class="btn-text">✨ AI-Write Bullet Points</span>
    </button>
    <textarea class="form-control exp-desc" rows="4" placeholder="Describe your responsibilities and achievements..."></textarea>
  `;
  div.querySelectorAll('input, textarea').forEach(el => el.addEventListener('input', updatePreview));
  document.getElementById('exp-list').appendChild(div);
  updatePreview();
}

// ── ADD PROJECT ──
let projCount = 0;
function addProject() {
  projCount++;
  const id = 'proj-' + projCount;
  const div = document.createElement('div');
  div.className = 'entry-card';
  div.id = id;
  div.innerHTML = `
    <button class="remove-btn" onclick="removeEntry('${id}')"><i class="fas fa-times"></i></button>
    <input type="text" class="form-control proj-name" placeholder="Project Name">
    <input type="text" class="form-control proj-tech" placeholder="Technologies Used (e.g. React, Node.js, MongoDB)">
    <button class="ai-gen-btn" style="width:100%;justify-content:center;" onclick="generateProjBullets(this, '${id}')">
      <span class="spinner"></span><span class="btn-text">✨ AI-Enhance Description</span>
    </button>
    <textarea class="form-control proj-desc" rows="3" placeholder="Briefly describe the project, your role, and its impact..."></textarea>
  `;
  div.querySelectorAll('input, textarea').forEach(el => el.addEventListener('input', updatePreview));
  document.getElementById('proj-list').appendChild(div);
  updatePreview();
}

function removeEntry(id) {
  document.getElementById(id)?.remove();
  updatePreview();
}

// ── AI GENERATION ──
async function generateWithAI(section, targetId, btnId) {
  const btn = document.getElementById(btnId);
  if (!btn) return;
  btn.classList.add('loading');
  btn.disabled = true;

  const targetRole = document.getElementById('f-title')?.value || 'Software Developer';
  const extraData  = document.getElementById(targetId)?.value || '';

  try {
    const fd = new FormData();
    fd.append('action', 'generate_section');
    fd.append('section', section);
    fd.append('extra', extraData);
    fd.append('target_role', targetRole);

    const resp = await fetch('../php/ai-resume-builder-api.php', { method: 'POST', body: fd });
    const data = await resp.json();

    if (data.success) {
      let content = data.content;
      // Try parse as JSON array for bullet sections
      if (['skills_bullets','experience_bullets','project_description','education_summary'].includes(section)) {
        try {
          const arr = JSON.parse(content);
          if (Array.isArray(arr)) content = arr.join('\n');
        } catch(e) { /* keep as-is */ }
      }
      const el = document.getElementById(targetId);
      if (el) {
        el.value = content;
        el.dispatchEvent(new Event('input'));
      }
      showToast(data.is_ai ? '✨ AI content generated!' : '📝 Content filled (add API key for AI)', data.is_ai ? 'success' : 'info');
    } else {
      showToast('⚠️ ' + (data.error || 'AI generation failed'), 'error');
    }
  } catch(e) {
    showToast('❌ Network error: ' + e.message, 'error');
  } finally {
    btn.classList.remove('loading');
    btn.disabled = false;
  }
}

async function generateExpBullets(btn, cardId) {
  btn.classList.add('loading');
  btn.disabled = true;
  const card = document.getElementById(cardId);
  const desc = card?.querySelector('.exp-desc');
  const role = card?.querySelector('.exp-title')?.value || '';
  const targetRole = document.getElementById('f-title')?.value || 'Software Developer';

  try {
    const fd = new FormData();
    fd.append('action', 'generate_section');
    fd.append('section', 'experience_bullets');
    fd.append('extra', (role ? role + ': ' : '') + (desc?.value || ''));
    fd.append('target_role', targetRole);

    const resp = await fetch('../php/ai-resume-builder-api.php', { method: 'POST', body: fd });
    const data = await resp.json();

    if (data.success && desc) {
      let content = data.content;
      try {
        const arr = JSON.parse(content);
        if (Array.isArray(arr)) content = arr.join('\n');
      } catch(e) { /* keep as-is */ }
      desc.value = content;
      desc.dispatchEvent(new Event('input'));
      showToast('✨ Experience bullets generated!', 'success');
    } else {
      showToast('⚠️ ' + (data.error || 'Failed'), 'error');
    }
  } catch(e) { showToast('❌ Error: ' + e.message, 'error'); }
  finally { btn.classList.remove('loading'); btn.disabled = false; }
}

async function generateProjBullets(btn, cardId) {
  btn.classList.add('loading');
  btn.disabled = true;
  const card = document.getElementById(cardId);
  const desc = card?.querySelector('.proj-desc');
  const name = card?.querySelector('.proj-name')?.value || '';
  const tech = card?.querySelector('.proj-tech')?.value || '';
  const targetRole = document.getElementById('f-title')?.value || 'Software Developer';

  try {
    const fd = new FormData();
    fd.append('action', 'generate_section');
    fd.append('section', 'project_description');
    fd.append('extra', (name ? name + ': ' : '') + (desc?.value || '') + (tech ? ' (Tech: ' + tech + ')' : ''));
    fd.append('target_role', targetRole);

    const resp = await fetch('../php/ai-resume-builder-api.php', { method: 'POST', body: fd });
    const data = await resp.json();

    if (data.success && desc) {
      let content = data.content;
      try { const arr = JSON.parse(content); if (Array.isArray(arr)) content = arr.join('\n'); } catch(e) {}
      desc.value = content;
      desc.dispatchEvent(new Event('input'));
      showToast('✨ Project description enhanced!', 'success');
    } else {
      showToast('⚠️ ' + (data.error || 'Failed'), 'error');
    }
  } catch(e) { showToast('❌ Error: ' + e.message, 'error'); }
  finally { btn.classList.remove('loading'); btn.disabled = false; }
}

// ── PREVIEW RENDER ──
function updatePreview() {
  const data = gatherData();
  const html = renderResume(data);
  document.getElementById('resume-preview').className = state.template;
  document.getElementById('resume-preview').innerHTML = html;
}

function gatherData() {
  // Education
  const eduItems = [];
  document.querySelectorAll('#edu-list .entry-card').forEach(card => {
    eduItems.push({
      degree: card.querySelector('.edu-degree')?.value || '',
      school: card.querySelector('.edu-school')?.value || '',
      year:   card.querySelector('.edu-year')?.value   || '',
      marks:  card.querySelector('.edu-marks')?.value  || ''
    });
  });

  // Experience
  const expItems = [];
  document.querySelectorAll('#exp-list .entry-card').forEach(card => {
    expItems.push({
      title:   card.querySelector('.exp-title')?.value   || '',
      company: card.querySelector('.exp-company')?.value || '',
      period:  card.querySelector('.exp-period')?.value  || '',
      desc:    card.querySelector('.exp-desc')?.value    || ''
    });
  });

  // Projects
  const projItems = [];
  document.querySelectorAll('#proj-list .entry-card').forEach(card => {
    projItems.push({
      name: card.querySelector('.proj-name')?.value || '',
      tech: card.querySelector('.proj-tech')?.value || '',
      desc: card.querySelector('.proj-desc')?.value || ''
    });
  });

  return {
    name:      getElementById('f-name'),
    title:     getElementById('f-title'),
    email:     getElementById('f-email'),
    phone:     getElementById('f-phone'),
    location:  getElementById('f-location'),
    links:     getElementById('f-links'),
    summary:   getElementById('f-summary'),
    skills:    getElementById('f-skills'),
    education: eduItems,
    experience:expItems,
    projects:  projItems,
    certs:     getElementById('f-certs')
  };
}

function getElementById(id) {
  return document.getElementById(id)?.value?.trim() || '';
}

function renderResume(d) {
  const tmpl = state.template;

  // Skills: parse comma or newline
  const skillsList = d.skills.split(/[,\n]/).map(s => s.trim()).filter(Boolean);

  // Initials for avatar
  const initials = (d.name || 'RB').split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);

  const contactItems = [
    d.email    ? `<span><i class="fas fa-envelope"></i> ${esc(d.email)}</span>` : '',
    d.phone    ? `<span><i class="fas fa-phone"></i> ${esc(d.phone)}</span>` : '',
    d.location ? `<span><i class="fas fa-map-marker-alt"></i> ${esc(d.location)}</span>` : '',
    d.links    ? `<span><i class="fas fa-link"></i> ${esc(d.links)}</span>` : '',
  ].filter(Boolean).join('');

  // Shared sub-renderers
  const renderSkillTags = () => skillsList.map(s => `<span class="skill-tag">${esc(s)}</span>`).join('');
  
  const renderEdu = () => d.education.map(e => e.degree ? `
    <div class="edu-item">
      <div class="edu-degree">${esc(e.degree)}</div>
      ${e.school ? `<div class="edu-detail">${esc(e.school)}</div>` : ''}
      <div class="edu-detail">${[e.year, e.marks].filter(Boolean).map(esc).join(' · ')}</div>
    </div>` : '').join('');

  const renderExp = () => d.experience.map(e => e.title ? `
    <div class="exp-item">
      <div class="exp-title">${esc(e.title)}</div>
      <div class="exp-sub">${[e.company, e.period].filter(Boolean).map(esc).join(' · ')}</div>
      <ul class="exp-bullets">${bulletLines(e.desc)}</ul>
    </div>` : '').join('');

  const renderProj = () => d.projects.map(p => p.name ? `
    <div class="exp-item">
      <div class="exp-title">${esc(p.name)}</div>
      ${p.tech ? `<div class="exp-sub">${esc(p.tech)}</div>` : ''}
      <ul class="exp-bullets">${bulletLines(p.desc)}</ul>
    </div>` : '').join('');

  const certLines = d.certs ? d.certs.split('\n').filter(Boolean).map(c => `<li>${esc(c.trim())}</li>`).join('') : '';

  // ── Template-specific rendering ──
  if (tmpl === 'tmpl-classic') {
    return `
      <div class="resume-header">
        <div class="resume-name">${esc(d.name || 'Your Name')}</div>
        <div class="resume-title">${esc(d.title || 'Professional Title')}</div>
        <div class="resume-contact">${contactItems || '<span>Add your contact info</span>'}</div>
      </div>
      <div class="resume-body">
        <div class="sidebar">
          ${skillsList.length ? `<div class="section"><div class="section-title">Skills</div>${renderSkillTags()}</div>` : ''}
          ${d.education.some(e=>e.degree) ? `<div class="section"><div class="section-title">Education</div>${renderEdu()}</div>` : ''}
          ${certLines ? `<div class="section"><div class="section-title">Certifications</div><ul class="exp-bullets">${certLines}</ul></div>` : ''}
        </div>
        <div class="main-col">
          ${d.summary ? `<div class="section"><div class="section-title">Professional Summary</div><p class="summary-text">${esc(d.summary)}</p></div>` : ''}
          ${d.experience.some(e=>e.title) ? `<div class="section"><div class="section-title">Work Experience</div>${renderExp()}</div>` : ''}
          ${d.projects.some(p=>p.name)    ? `<div class="section"><div class="section-title">Projects</div>${renderProj()}</div>` : ''}
        </div>
      </div>`;
  }

  if (tmpl === 'tmpl-modern') {
    return `
      <div class="resume-header">
        <div class="avatar-circle">${initials}</div>
        <div>
          <div class="resume-name">${esc(d.name || 'Your Name')}</div>
          <div class="resume-title">${esc(d.title || 'Professional Title')}</div>
          <div class="resume-contact">${contactItems || '<span>Add your contact info</span>'}</div>
        </div>
      </div>
      <div class="resume-body">
        <div class="sidebar">
          ${skillsList.length ? `<div class="section"><div class="section-title">Skills</div>${renderSkillTags()}</div>` : ''}
          ${d.education.some(e=>e.degree) ? `<div class="section"><div class="section-title">Education</div>${renderEdu()}</div>` : ''}
          ${certLines ? `<div class="section"><div class="section-title">Certifications</div><ul class="exp-bullets">${certLines}</ul></div>` : ''}
        </div>
        <div class="main-col">
          ${d.summary ? `<div class="section"><div class="section-title">Professional Summary</div><p class="summary-text">${esc(d.summary)}</p></div>` : ''}
          ${d.experience.some(e=>e.title) ? `<div class="section"><div class="section-title">Experience</div>${renderExp()}</div>` : ''}
          ${d.projects.some(p=>p.name)    ? `<div class="section"><div class="section-title">Projects</div>${renderProj()}</div>` : ''}
        </div>
      </div>`;
  }

  if (tmpl === 'tmpl-minimal') {
    return `
      <div class="resume-header">
        <div class="resume-name">${esc(d.name || 'Your Name')}</div>
        <div class="resume-title">${esc(d.title || 'Professional Title')}</div>
        <div class="resume-contact">${contactItems || '<span>Add your contact info</span>'}</div>
      </div>
      <div class="resume-body">
        ${d.summary ? `<div class="section"><div class="section-title">Summary</div><p class="summary-text">${esc(d.summary)}</p></div>` : ''}
        ${skillsList.length ? `<div class="section"><div class="section-title">Technical Skills</div>${renderSkillTags()}</div>` : ''}
        ${d.experience.some(e=>e.title) ? `<div class="section"><div class="section-title">Experience</div>${renderExp()}</div>` : ''}
        ${d.projects.some(p=>p.name)    ? `<div class="section"><div class="section-title">Projects</div>${renderProj()}</div>` : ''}
        ${d.education.some(e=>e.degree) ? `<div class="section"><div class="section-title">Education</div>${renderEdu()}</div>` : ''}
        ${certLines ? `<div class="section"><div class="section-title">Certifications & Achievements</div><ul class="exp-bullets">${certLines}</ul></div>` : ''}
      </div>`;
  }

  if (tmpl === 'tmpl-creative') {
    return `
      <div class="resume-header">
        <div>
          <div class="resume-name">${esc(d.name || 'Your Name')}</div>
          <div class="resume-title">${esc(d.title || 'Professional Title')}</div>
        </div>
        <div class="header-right">
          <div class="resume-contact">${contactItems || ''}</div>
        </div>
      </div>
      <div class="resume-body">
        <div class="sidebar">
          ${skillsList.length ? `<div class="section"><div class="section-title">Skills</div>${renderSkillTags()}</div>` : ''}
          ${d.education.some(e=>e.degree) ? `<div class="section"><div class="section-title">Education</div>${renderEdu()}</div>` : ''}
          ${certLines ? `<div class="section"><div class="section-title">Certifications</div><ul class="exp-bullets">${certLines}</ul></div>` : ''}
        </div>
        <div class="main-col">
          ${d.summary ? `<div class="section"><div class="section-title">About Me</div><p class="summary-text">${esc(d.summary)}</p></div>` : ''}
          ${d.experience.some(e=>e.title) ? `<div class="section"><div class="section-title">Experience</div>${renderExp()}</div>` : ''}
          ${d.projects.some(p=>p.name)    ? `<div class="section"><div class="section-title">Projects</div>${renderProj()}</div>` : ''}
        </div>
      </div>`;
  }

  return '<p>Select a template above.</p>';
}

function bulletLines(text) {
  if (!text) return '';
  return text.split('\n').filter(Boolean).map(l => `<li>${esc(l.trim().replace(/^[-•*]\s*/, ''))}</li>`).join('');
}

function esc(str) {
  if (!str) return '';
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escapeAttr(str) {
  if (!str) return '';
  return str.replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── PDF DOWNLOAD ──
async function downloadPDF() {
  showToast('📄 Generating PDF...', 'info');
  try {
    const { jsPDF } = window.jspdf;
    const preview = document.getElementById('resume-preview');
    const canvas  = await html2canvas(preview, { scale: 2, useCORS: true, backgroundColor: '#fff' });
    const imgData = canvas.toDataURL('image/jpeg', 0.97);
    const pdf = new jsPDF('p', 'mm', 'a4');
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const imgH  = (canvas.height * pageW) / canvas.width;
    
    if (imgH <= pageH) {
      pdf.addImage(imgData, 'JPEG', 0, 0, pageW, imgH);
    } else {
      // Multi-page
      let pos = 0;
      while (pos < imgH) {
        pdf.addImage(imgData, 'JPEG', 0, -pos, pageW, imgH);
        pos += pageH;
        if (pos < imgH) pdf.addPage();
      }
    }
    const name = document.getElementById('f-name')?.value?.trim().replace(/\s+/g, '_') || 'Resume';
    pdf.save(name + '_Resume.pdf');
    showToast('✅ PDF downloaded!', 'success');
  } catch(e) {
    showToast('❌ PDF error: ' + e.message, 'error');
  }
}

// ── TOAST ──
function showToast(msg, type = 'info') {
  const c = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast-msg ${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
