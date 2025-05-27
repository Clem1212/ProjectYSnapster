<?php
session_start();

// Files
$users_file = 'users.json';
$messages_file = 'messages.json';

$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$messages = file_exists($messages_file) ? json_decode(file_get_contents($messages_file), true) : [];

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

function save_users($users) {
    file_put_contents('users.json', json_encode($users, JSON_PRETTY_PRINT));
}
function save_messages($messages) {
    file_put_contents('messages.json', json_encode($messages, JSON_PRETTY_PRINT));
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: chat_app.php");
    exit;
}

// Login/Register
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $found_user = null;

    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $found_user = $user;
            break;
        }
    }

    if ($found_user && isset($found_user['friend_code'])) {
        $_SESSION['user'] = $found_user;
    } else {
        $new_user = [
            'id' => uniqid(),
            'username' => $username,
            'friend_code' => strtoupper(substr(md5(uniqid()), 0, 8))
        ];
        $users[] = $new_user;
        save_users($users);
        $_SESSION['user'] = $new_user;
    }
}

// Handle photo upload
if (isset($_POST['send_photo']) && isset($_SESSION['user'])) {
    $to_code = trim($_POST['receiver_code']);
    $photo_data = $_POST['photo_data'];
    
    $receiver = null;
    foreach ($users as $u) {
        if ($u['friend_code'] === $to_code) {
            $receiver = $u;
            break;
        }
    }

    if ($receiver && $photo_data) {
        // Remove data:image/jpeg;base64, prefix
        $photo_data = str_replace('data:image/jpeg;base64,', '', $photo_data);
        $photo_data = str_replace(' ', '+', $photo_data);
        $decoded_photo = base64_decode($photo_data);
        
        // Generate unique filename
        $filename = 'photo_' . uniqid() . '.jpg';
        $filepath = 'uploads/' . $filename;
        
        // Save photo
        if (file_put_contents($filepath, $decoded_photo)) {
            $messages[] = [
                'from' => $_SESSION['user']['id'],
                'to' => $receiver['id'],
                'message' => '',
                'photo' => $filepath,
                'sender_name' => $_SESSION['user']['username'],
                'timestamp' => date("Y-m-d H:i:s"),
                'type' => 'photo'
            ];
            save_messages($messages);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save photo']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid receiver or photo data']);
        exit;
    }
}

// Send text message
if (isset($_POST['send_message']) && isset($_SESSION['user'])) {
    $to_code = trim($_POST['receiver_code']);
    $text = trim($_POST['message']);

    $receiver = null;
    foreach ($users as $u) {
        if ($u['friend_code'] === $to_code) {
            $receiver = $u;
            break;
        }
    }

    if ($receiver) {
        $messages[] = [
            'from' => $_SESSION['user']['id'],
            'to' => $receiver['id'],
            'message' => $text,
            'sender_name' => $_SESSION['user']['username'],
            'timestamp' => date("Y-m-d H:i:s"),
            'type' => 'text'
        ];
        save_messages($messages);
        header("Location: chat_app.php?chat_with=" . $receiver['id']);
        exit;
    } else {
        $error = "Friend code not found.";
    }
}

// Active chat
$chat_with = isset($_GET['chat_with']) ? $_GET['chat_with'] : null;

// Get message list with selected user
$chat_messages = [];
if (isset($_SESSION['user']) && $chat_with) {
    $uid = $_SESSION['user']['id'];
    foreach ($messages as $msg) {
        if (
            ($msg['from'] === $uid && $msg['to'] === $chat_with) ||
            ($msg['to'] === $uid && $msg['from'] === $chat_with)
        ) {
            $chat_messages[] = $msg;
        }
    }
}

// Build chat list
$chat_users = [];
if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    foreach ($messages as $msg) {
        $other_id = $msg['from'] === $uid ? $msg['to'] : ($msg['to'] === $uid ? $msg['from'] : null);
        if ($other_id && $other_id !== $uid && !isset($chat_users[$other_id])) {
            foreach ($users as $u) {
                if ($u['id'] === $other_id) {
                    $chat_users[$other_id] = $u;
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Private JSON Chat</title>
     <link rel="stylesheet" href="/frontend/style.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* General styling */
        .con {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
                Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
           
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            color:rgb(122, 119, 119);
        }

        h2 {
            font-weight: 700;
            font-size: 2em;
            margin-bottom: 20px;
        }

        /* Friend code */
        p {
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        strong {
            color: #0095f6; /* Instagram blue */
        }

        /* Form inputs */
        input[type="text"], textarea {
            width: 100%;
            padding: 12px 15px;
            margin: 8px 0;
            border: 1px solid #dbdbdb;
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
            resize: vertical;
            transition: border-color 0.2s ease-in-out;
        }
        input[type="text"]:focus, textarea:focus {
            border-color: #0095f6;
            outline: none;
        }

        button {
            background-color: #0095f6;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 1em;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }
        button:hover {
            background-color: #0077cc;
        }

        /* Error message */
        p.error {
            color: #ed4956;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Chat tabs */
        .chat-tabs {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border-bottom: 2px solid #efefef;
            padding-bottom: 10px;
        }
        .chat-tabs a {
            padding: 8px 16px;
            background: #fafafa;
            border-radius: 20px;
            text-decoration: none;
            color: #262626;
            font-weight: 600;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.1);
            transition: background-color 0.2s ease-in-out;
        }
        .chat-tabs a.active {
            background-color: #0095f6;
            color: white;
            box-shadow: none;
        }
        .chat-tabs a:hover:not(.active) {
            background-color: #dbdbdb;
        }

        /* Chat bubbles */
        .chat-box {
            margin-top: 25px;
            min-height: 180px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }
        .message-bubble {
            max-width: 70%;
            margin: 10px 0;
            padding: 14px 18px;
            border-radius: 20px;
            font-size: 1em;
            line-height: 1.3;
            word-wrap: break-word;
            box-shadow: 0 1px 1.5px rgb(0 0 0 / 0.1);
            position: relative;
            clear: both;
        }
        .sent {
            background:rgb(58, 56, 56);
            float: right;
            text-align: right;
            border-bottom-right-radius: 4px;
        }
        .received {
            background:rgb(184, 182, 182);
            float: left;
            text-align: left;
            border-bottom-left-radius: 4px;
        }
        .timestamp {
            display: block;
            font-size: 0.75em;
            color: #999;
            margin-top: 6px;
            font-weight: 500;
            opacity: 0.7;
        }

        .clearfix {
            clear: both;
        }

        /* Photo message styles */
        .photo-message {
            padding: 5px;
            border-radius: 15px;
            overflow: hidden;
        }
        .photo-message img {
            width: 100%;
            max-width: 250px;
            height: auto;
            border-radius: 10px;
            display: block;
        }

        /* Camera modal */
        .camera-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.9);
            overflow: auto;
        }

        .camera-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        #camera-video {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            margin: 10px 0;
        }

        #camera-canvas {
            display: none;
        }

        .camera-controls {
            margin: 15px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .camera-btn {
            background-color: #0095f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }

        .camera-btn:hover {
            background-color: #0077cc;
        }

        .camera-btn.capture {
            background-color: #ff3040;
        }

        .camera-btn.capture:hover {
            background-color: #cc2633;
        }

        .photo-controls {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }

        /* Photo button in message form */
        .message-form {
            position: relative;
        }

        .photo-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #0095f6;
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
        }

        .photo-btn:hover {
            color: #0077cc;
        }

        /* Logout */
        .logout {
            margin-top: 30px;
            text-align: center;
        }
        .logout a {
            color: #ed4956;
            text-decoration: none;
            font-weight: 700;
            font-size: 1em;
            transition: color 0.2s ease-in-out;
        }
        .logout a:hover {
            color: #a81d29;
        }

        /* Message input container */
        .message-input-container {
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 10px;
        }

        .message-input-container textarea {
            flex: 1;
            margin: 0;
        }

        .send-btn {
            background-color: #0095f6;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            height: 45px;
        }

        .send-btn:hover {
            background-color: #0077cc;
        }

        .camera-icon-btn {
            background-color: #ff3040;
            color: white;
            border: none;
            padding: 12px 15px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 45px;
            height: 45px;
        }

        .camera-icon-btn:hover {
            background-color: #cc2633;
        }
      
    </style>
    <style>@media (min-width: 768px) {
  .bottom-nav { 
    display: none !important; /* optional: ensure it wins */ 
  }
}


    .container {
      max-width: 600px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    /* Header */
    .app-header {
      position: sticky;
      top: 0;
      background-color: var(--header-bg);
      border-bottom: 1px solid var(--border-color);
      padding: 12px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 100;
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    
    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .logo {
      font-size: 22px;
      font-weight: 700;
      color: var(--text-color);
      text-decoration: none;
    }
    
    .header-icons {
      display: flex;
      gap: 15px;
    }
    
    .header-icon {
      font-size: 20px;
      color: var(--text-color);
      cursor: pointer;
    }
    
    /* Theme switch */
    .theme-switch {
      display: flex;
      align-items: center;
      cursor: pointer;
      margin-left: auto;
      margin-right: 16px;
    }
    
    .theme-icon {
      font-size: 18px;
      color: var(--text-color);
    }
     .type {
      font-size: 18px;
      color: var(--text-color);
    }
 </style>

</head>
<body>
     <div class="app-header">
    <div class="header-content">
        <!--<div class="logo"> <div class="logo"><img src="/image/border1.png" style="width: 200px;height: auto;" ></div> </div>-->
<div class="logo">
 <img id="logo-img" src="/image/border2.png" style="width: 120px; height: auto;">
</div>    
<!-- <img id="logo-img" src="/image/border1.png" style="width: 120px; height: auto;">-->


  <div class="theme-switch" id="themeSwitch">
        <i class="fa-solid fa-sun theme-icon"></i>
      </div>
      <div class="header-icons" >
       <a href="/frontend/feed.html">
        <i class="fa-solid fa-bell header-icon" ></i></a> 
       <a href="/frontend/chat_app.php">
        <i class="fa-solid fa-message header-icon"></i></a>
         <a id="logoutBtn" class="logout-btn" href="/frontend/index.html">
      <i class="fa-solid fa-right-from-bracket" style="font-size: 25px;color: var(--text-color);"></i>
        </a>
      </div>
     -
    </div>
  </div>
  <hr>

<!-- Camera Modal -->
<div id="cameraModal" class="camera-modal">
    <div class="camera-content">
        <span class="close">&times;</span>
        <h3>Take a Photo</h3>
        <video id="camera-video" autoplay></video>
        <canvas id="camera-canvas"></canvas>
        <div class="camera-controls">
            <button id="capture-btn" class="camera-btn capture">
                <i class="fa-solid fa-camera"></i> Capture
            </button>
            <button id="switch-camera-btn" class="camera-btn">
                <i class="fa-solid fa-refresh"></i> Switch Camera
            </button>
        </div>
        <div id="photo-preview" style="display: none;">
            <img id="preview-image" style="max-width: 100%; border-radius: 10px;">
            <div class="photo-controls">
                <button id="retake-btn" class="camera-btn">Retake</button>
                <button id="send-photo-btn" class="camera-btn">Send Photo</button>
            </div>
        </div>
    </div>
</div>

<script>// Apply saved theme on page load
window.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('theme');
  const body = document.body;
  const themeSwitch = document.getElementById('themeSwitch');

  if (savedTheme === 'instagram') {
    body.classList.add('instagram-theme');
    themeSwitch.innerHTML = '<i class="fa-solid fa-moon theme-icon"></i>';
  } else {
    body.classList.add('bereal-theme');
    themeSwitch.innerHTML = '<i class="fa-solid fa-sun theme-icon"></i>';
  }
});

    // Theme switcher
    const themeSwitch = document.getElementById('themeSwitch');
   themeSwitch.addEventListener('click', () => {
  const body = document.body;
  if (body.classList.contains('bereal-theme')) {
    body.classList.remove('bereal-theme');
    body.classList.add('instagram-theme');
    themeSwitch.innerHTML = '<i class="fa-solid fa-moon theme-icon"></i>';
    localStorage.setItem('theme', 'instagram');
  } else {
    body.classList.remove('instagram-theme');
    body.classList.add('bereal-theme');
    themeSwitch.innerHTML = '<i class="fa-solid fa-sun theme-icon"></i>';
    localStorage.setItem('theme', 'bereal');
  }
});

// Camera functionality
let currentStream = null;
let currentCamera = 'user'; // 'user' for front camera, 'environment' for back camera
let capturedPhoto = null;
let currentReceiverCode = null;

const modal = document.getElementById('cameraModal');
const video = document.getElementById('camera-video');
const canvas = document.getElementById('camera-canvas');
const captureBtn = document.getElementById('capture-btn');
const switchCameraBtn = document.getElementById('switch-camera-btn');
const retakeBtn = document.getElementById('retake-btn');
const sendPhotoBtn = document.getElementById('send-photo-btn');
const closeBtn = document.querySelector('.close');
const photoPreview = document.getElementById('photo-preview');
const previewImage = document.getElementById('preview-image');

// Open camera modal
function openCamera(receiverCode) {
    currentReceiverCode = receiverCode;
    modal.style.display = 'block';
    startCamera();
}

// Close camera modal
function closeCamera() {
    modal.style.display = 'none';
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    photoPreview.style.display = 'none';
    video.style.display = 'block';
    document.querySelector('.camera-controls').style.display = 'block';
}

// Start camera
async function startCamera() {
    try {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        
        const constraints = {
            video: {
                facingMode: currentCamera,
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };
        
        currentStream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = currentStream;
    } catch (error) {
        console.error('Error accessing camera:', error);
        alert('Could not access camera. Please check permissions.');
    }
}

// Capture photo
function capturePhoto() {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    
    capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
    previewImage.src = capturedPhoto;
    
    video.style.display = 'none';
    document.querySelector('.camera-controls').style.display = 'none';
    photoPreview.style.display = 'block';
}

// Switch camera
function switchCamera() {
    currentCamera = currentCamera === 'user' ? 'environment' : 'user';
    startCamera();
}

// Retake photo
function retakePhoto() {
    photoPreview.style.display = 'none';
    video.style.display = 'block';
    document.querySelector('.camera-controls').style.display = 'block';
}

// Send photo
async function sendPhoto() {
    if (!capturedPhoto || !currentReceiverCode) {
        alert('No photo captured or receiver not specified');
        return;
    }
    
    const formData = new FormData();
    formData.append('send_photo', '1');
    formData.append('receiver_code', currentReceiverCode);
    formData.append('photo_data', capturedPhoto);
    
    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeCamera();
            location.reload(); // Refresh to show the new photo message
        } else {
            alert('Failed to send photo: ' + (result.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error sending photo:', error);
        alert('Failed to send photo');
    }
}

// Event listeners
captureBtn.addEventListener('click', capturePhoto);
switchCameraBtn.addEventListener('click', switchCamera);
retakeBtn.addEventListener('click', retakePhoto);
sendPhotoBtn.addEventListener('click', sendPhoto);
closeBtn.addEventListener('click', closeCamera);

// Close modal when clicking outside
window.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeCamera();
    }
});
</script>
<div class="con"> 
<h2>Private Chat App</h2>

<?php if (!isset($_SESSION['user'])): ?>
    <form method="POST">
        <input type="text" name="username" required placeholder="Enter your username">
        <button type="submit" name="login">Login / Register</button>
    </form>
    
<?php else: ?>
    <p>
        Welcome, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong><br>
        Your Friend Code:
        <?php if (!empty($_SESSION['user']['friend_code'])): ?>
            <strong><?= htmlspecialchars($_SESSION['user']['friend_code']) ?></strong>
        <?php else: ?>
            <span style="color:red;">(Friend code not set. Try logging out and back in.)</span>
        <?php endif; ?>
    </p>
 <div class="logout">
        <a href="?logout=1">Logout</a>
    </div><br>

    <form method="POST" id="messageForm">
        <input type="text" name="receiver_code" id="receiver_code" placeholder="Enter Friend Code to Start Chat" required>
        <div class="message-input-container">
            <textarea name="message" placeholder="Type your message..." required></textarea>
            <button type="button" class="camera-icon-btn" onclick="openCamera(document.getElementById('receiver_code').value)">
                <i class="fa-solid fa-camera"></i>
            </button>
            <button type="submit" name="send_message" class="send-btn">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
    </form>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (!empty($chat_users)): ?>
        <div class="chat-tabs">
            <?php foreach ($chat_users as $id => $user): ?>
                <a href="?chat_with=<?= $id ?>" class="<?= ($chat_with === $id ? 'active' : '') ?>">
                    <?= htmlspecialchars($user['username']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="chat-box">
        <?php if ($chat_with && !empty($chat_messages)): ?>
            <?php foreach ($chat_messages as $msg): ?>
                <?php
                    $is_sent = $msg['from'] === $_SESSION['user']['id'];
                    $bubble_class = $is_sent ? 'sent' : 'received';
                    $message_type = isset($msg['type']) ? $msg['type'] : 'text';
                ?>
                <div class="message-bubble <?= $bubble_class ?> <?= $message_type === 'photo' ? 'photo-message' : '' ?>">
                    <?php if ($message_type === 'photo' && isset($msg['photo'])): ?>
                        <img src="<?= htmlspecialchars($msg['photo']) ?>" alt="Photo message" style="max-width: 250px; border-radius: 10px;">
                    <?php else: ?>
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    <?php endif; ?>
                    <span class="timestamp"><?= htmlspecialchars($msg['sender_name']) ?> • <?= $msg['timestamp'] ?></span>
                </div>
                <div class="clearfix"></div>
            <?php endforeach; ?>
        <?php elseif ($chat_with): ?>
            <p>No messages yet. Say hi or send a photo!</p>
        <?php else: ?>
            <p>Select a user to view your chat.</p>
        <?php endif; ?>
    </div>

   </div>
<?php endif; ?>
<!-- Place this right before closing </body> tag -->

<nav class="bottom-nav">
  <a href="feed.html" class="nav-item" aria-label="Home">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z"/></svg>
    <span>Home</span>
  </a>
  <a href="/frontend/search.php" class="nav-item" aria-label="Search">
<svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
  <circle cx="11" cy="11" r="7" />
  <line x1="21" y1="21" x2="16.65" y2="16.65" />
</svg>
  <span>Search</span>
    </a>
  <a href="upload.html" class="nav-item" aria-label="Upload">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v12m0 0l-4-4m4 4l4-4"/><path d="M4 20h16"/></svg>
    <span>Upload</span>
  </a>
  <a href="chat_app.php" class="nav-item" aria-label="Alerts">
    <svg xmlns="http://www.w3.org/2000/svg
    <a href="chat_app.php" class="nav-item active" aria-label="Messages">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    <span>Messages</span>
  </a>
  <a href="profile.html" class="nav-item" aria-label="Profile">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <span>Profile</span>
  </a>
</nav>

<style>
/* Enhanced photo capture styles */
.photo-capture-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.photo-capture-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    height: 80vh;
    background: #000;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.photo-capture-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 10;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, transparent 100%);
}

.capture-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.capture-flash-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.capture-flash-btn.active {
    background: rgba(255, 255, 0, 0.8);
    color: #000;
}

.photo-capture-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}

.photo-capture-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 30px;
    background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.capture-gallery-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.capture-main-btn {
    background: white;
    border: 4px solid rgba(255, 255, 255, 0.3);
    color: #000;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 24px;
    transition: transform 0.1s ease;
}

.capture-main-btn:active {
    transform: scale(0.9);
}

.capture-switch-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

/* Photo preview styles */
.photo-preview-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #000;
    z-index: 9999;
    display: none;
    flex-direction: column;
}

.photo-preview-header {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 10;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, transparent 100%);
}

.preview-back-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(10px);
}

.photo-preview-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #000;
}

.photo-preview-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 30px;
    background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-retake-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    cursor: pointer;
    backdrop-filter: blur(10px);
    font-weight: 600;
}

.preview-send-btn {
    background: #0095f6;
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.preview-send-btn:hover {
    background: #0077cc;
}

/* Flash effect */
.flash-effect {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    z-index: 10000;
    opacity: 0;
    pointer-events: none;
}

.flash-effect.active {
    opacity: 0.8;
    transition: opacity 0.1s ease-out;
}

/* Enhanced message input */
.enhanced-message-input {
    position: relative;
    background: var(--surface-color);
    border-radius: 25px;
    padding: 8px;
    border: 1px solid var(--border-color);
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.enhanced-message-textarea {
    flex: 1;
    border: none;
    background: none;
    resize: none;
    padding: 8px 12px;
    font-size: 16px;
    color: var(--text-color);
    max-height: 100px;
    min-height: 20px;
    outline: none;
}

.enhanced-message-textarea::placeholder {
    color: var(--text-secondary);
}

.quick-camera-btn {
    background: linear-gradient(45deg, #ff3040, #ff6b7d);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: transform 0.1s ease;
    box-shadow: 0 2px 8px rgba(255, 48, 64, 0.3);
}

.quick-camera-btn:active {
    transform: scale(0.9);
}

.quick-send-btn {
    background: #0095f6;
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: transform 0.1s ease;
}

.quick-send-btn:active {
    transform: scale(0.9);
}

/* Photo message enhancements */
.photo-message-container {
    max-width: 250px;
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
}

.photo-message-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 18px;
}

.photo-message-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(0deg, rgba(0,0,0,0.6) 0%, transparent 100%);
    padding: 8px 12px;
    color: white;
    font-size: 12px;
    font-weight: 500;
}

/* Loading states */
.sending-photo {
    opacity: 0.7;
    pointer-events: none;
}

.photo-loading {
    position: relative;
}

.photo-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Mobile responsive */
@media (max-width: 480px) {
    .photo-capture-container {
        width: 100vw;
        height: 100vh;
        border-radius: 0;
    }
    
    .photo-capture-video {
        border-radius: 0;
    }
}
</style>

<script>
// Enhanced camera functionality
class SnapChatCamera {
    constructor() {
        this.currentStream = null;
        this.currentCamera = 'user';
        this.capturedPhoto = null;
        this.currentReceiverCode = null;
        this.flashEnabled = false;
        this.isCapturing = false;
        
        this.initializeElements();
        this.bindEvents();
    }
    
    initializeElements() {
        // Create photo capture overlay
        this.createCaptureOverlay();
        this.createPreviewOverlay();
        this.createFlashEffect();
        
        // Get existing elements
        this.messageForm = document.getElementById('messageForm');
        this.receiverCodeInput = document.getElementById('receiver_code');
    }
    
    createCaptureOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'photo-capture-overlay';
        overlay.id = 'photoCaptureOverlay';
        overlay.innerHTML = `
            <div class="photo-capture-container">
                <div class="photo-capture-header">
                    <button class="capture-close-btn" id="captureCloseBtn">
                        <i class="fa-solid fa-times"></i>
                    </button>
                    <button class="capture-flash-btn" id="captureFlashBtn">
                        <i class="fa-solid fa-bolt"></i>
                    </button>
                </div>
                <video class="photo-capture-video" id="photoCaptureVideo" autoplay playsinline></video>
                <div class="photo-capture-controls">
                    <button class="capture-gallery-btn" id="captureGalleryBtn">
                        <i class="fa-solid fa-images"></i>
                    </button>
                    <button class="capture-main-btn" id="captureMainBtn">
                        <i class="fa-solid fa-camera"></i>
                    </button>
                    <button class="capture-switch-btn" id="captureSwitchBtn">
                        <i class="fa-solid fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
        
        this.captureOverlay = overlay;
        this.captureVideo = document.getElementById('photoCaptureVideo');
    }
    
    createPreviewOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'photo-preview-overlay';
        overlay.id = 'photoPreviewOverlay';
        overlay.innerHTML = `
            <div class="photo-preview-header">
                <button class="preview-back-btn" id="previewBackBtn">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
            </div>
            <img class="photo-preview-image" id="photoPreviewImage" alt="Captured photo">
            <div class="photo-preview-controls">
                <button class="preview-retake-btn" id="previewRetakeBtn">
                    <i class="fa-solid fa-redo"></i> Retake
                </button>
                <button class="preview-send-btn" id="previewSendBtn">
                    <i class="fa-solid fa-paper-plane"></i> Send
                </button>
            </div>
        `;
        document.body.appendChild(overlay);
        
        this.previewOverlay = overlay;
        this.previewImage = document.getElementById('photoPreviewImage');
    }
    
    createFlashEffect() {
        const flash = document.createElement('div');
        flash.className = 'flash-effect';
        flash.id = 'flashEffect';
        document.body.appendChild(flash);
        this.flashEffect = flash;
    }
    
    bindEvents() {
        // Capture overlay events
        document.getElementById('captureCloseBtn').addEventListener('click', () => this.closeCapture());
        document.getElementById('captureFlashBtn').addEventListener('click', () => this.toggleFlash());
        document.getElementById('captureMainBtn').addEventListener('click', () => this.capturePhoto());
        document.getElementById('captureSwitchBtn').addEventListener('click', () => this.switchCamera());
        document.getElementById('captureGalleryBtn').addEventListener('click', () => this.openGallery());
        
        // Preview overlay events
        document.getElementById('previewBackBtn').addEventListener('click', () => this.backToCapture());
        document.getElementById('previewRetakeBtn').addEventListener('click', () => this.retakePhoto());
        document.getElementById('previewSendBtn').addEventListener('click', () => this.sendPhoto());
        
        // Enhanced message input events
        this.enhanceMessageInput();
    }
    
    enhanceMessageInput() {
        const messageContainer = document.querySelector('.message-input-container');
        if (messageContainer) {
            messageContainer.className = 'enhanced-message-input';
            
            const textarea = messageContainer.querySelector('textarea');
            textarea.className = 'enhanced-message-textarea';
            
            const cameraBtn = messageContainer.querySelector('.camera-icon-btn');
            cameraBtn.className = 'quick-camera-btn';
            cameraBtn.innerHTML = '<i class="fa-solid fa-camera"></i>';
            
            const sendBtn = messageContainer.querySelector('.send-btn');
            sendBtn.className = 'quick-send-btn';
            
            // Auto-resize textarea
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = Math.min(textarea.scrollHeight, 100) + 'px';
            });
        }
    }
    
    async openCapture(receiverCode) {
        if (!receiverCode) {
            alert('Please enter a friend code first');
            return;
        }
        
        this.currentReceiverCode = receiverCode;
        this.captureOverlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        try {
            await this.startCamera();
        } catch (error) {
            console.error('Failed to start camera:', error);
            alert('Could not access camera. Please check permissions.');
            this.closeCapture();
        }
    }
    
    closeCapture() {
        this.captureOverlay.style.display = 'none';
        this.previewOverlay.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        if (this.currentStream) {
            this.currentStream.getTracks().forEach(track => track.stop());
            this.currentStream = null;
        }
    }
    
    async startCamera() {
        try {
            if (this.currentStream) {
                this.currentStream.getTracks().forEach(track => track.stop());
            }
            
            const constraints = {
                video: {
                    facingMode: this.currentCamera,
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 }
                },
                audio: false
            };
            
            this.currentStream = await navigator.mediaDevices.getUserMedia(constraints);
            this.captureVideo.srcObject = this.currentStream;
            
            // Wait for video to be ready
            return new Promise((resolve) => {
                this.captureVideo.onloadedmetadata = () => resolve();
            });
        } catch (error) {
            throw new Error('Camera access failed: ' + error.message);
        }
    }
    
    toggleFlash() {
        this.flashEnabled = !this.flashEnabled;
        const flashBtn = document.getElementById('captureFlashBtn');
        
        if (this.flashEnabled) {
            flashBtn.classList.add('active');
            flashBtn.innerHTML = '<i class="fa-solid fa-bolt"></i>';
        } else {
            flashBtn.classList.remove('active');
            flashBtn.innerHTML = '<i class="fa-solid fa-bolt-slash"></i>';
        }
    }
    
    async switchCamera() {
        this.currentCamera = this.currentCamera === 'user' ? 'environment' : 'user';
        await this.startCamera();
    }
    
    async capturePhoto() {
        if (this.isCapturing) return;
        this.isCapturing = true;
        
        try {
            // Flash effect
            if (this.flashEnabled) {
                this.flashEffect.classList.add('active');
                setTimeout(() => this.flashEffect.classList.remove('active'), 150);
            }
            
            // Create canvas and capture
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            
            canvas.width = this.captureVideo.videoWidth;
            canvas.height = this.captureVideo.videoHeight;
            
            context.drawImage(this.captureVideo, 0, 0);
            
            // Convert to blob for better quality
            canvas.toBlob((blob) => {
                const reader = new FileReader();
                reader.onload = () => {
                    this.capturedPhoto = reader.result;
                    this.showPreview();
                };
                reader.readAsDataURL(blob);
            }, 'image/jpeg', 0.9);
            
        } catch (error) {
            console.error('Capture failed:', error);
            alert('Failed to capture photo');
        } finally {
            this.isCapturing = false;
        }
    }
    
    showPreview() {
        this.previewImage.src = this.capturedPhoto;
        this.captureOverlay.style.display = 'none';
        this.previewOverlay.style.display = 'flex';
    }
    
    backToCapture() {
        this.previewOverlay.style.display = 'none';
        this.captureOverlay.style.display = 'flex';
    }
    
    retakePhoto() {
        this.backToCapture();
    }
    
    async sendPhoto() {
        if (!this.capturedPhoto || !this.currentReceiverCode) {
            alert('No photo to send');
            return;
        }
        
        const sendBtn = document.getElementById('previewSendBtn');
        const originalText = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
        sendBtn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('send_photo', '1');
            formData.append('receiver_code', this.currentReceiverCode);
            formData.append('photo_data', this.capturedPhoto);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.closeCapture();
                // Refresh the page to show the new message
                window.location.reload();
            } else {
                throw new Error(result.error || 'Failed to send photo');
            }
        } catch (error) {
            console.error('Send failed:', error);
            alert('Failed to send photo: ' + error.message);
        } finally {
            sendBtn.innerHTML = originalText;
            sendBtn.disabled = false;
        }
    }
    
    openGallery() {
        // Create file input for gallery access
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => {
                    this.capturedPhoto = reader.result;
                    this.showPreview();
                };
                reader.readAsDataURL(file);
            }
        };
        input.click();
    }
}

// Initialize enhanced camera system
document.addEventListener('DOMContentLoaded', () => {
    window.snapCamera = new SnapChatCamera();
    
    // Update existing camera button to use new system
    const existingCameraBtn = document.querySelector('.camera-icon-btn, .quick-camera-btn');
    if (existingCameraBtn) {
        existingCameraBtn.onclick = () => {
            const receiverCode = document.getElementById('receiver_code').value.trim();
            window.snapCamera.openCapture(receiverCode);
        };
    }
    
    // Enhance photo messages display
    enhancePhotoMessages();
});

function enhancePhotoMessages() {
    const photoMessages = document.querySelectorAll('.photo-message');
    photoMessages.forEach(msg => {
        const img = msg.querySelector('img');
        if (img) {
            img.className = 'photo-message-image';
            
            // Add click to view full size
            img.style.cursor = 'pointer';
            img.onclick = () => viewFullSizePhoto(img.src);
        }
    });
}

function viewFullSizePhoto(src) {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        z-index: 10000;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = src;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 10px;
    `;
    
    overlay.appendChild(img);
    overlay.onclick = () => document.body.removeChild(overlay);
    
    document.body.appendChild(overlay);
}

// Service Worker for offline functionality (optional)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(console.error);
}
</script>

</body>
</html>