<?php
session_start();

// Files
$users_file = 'users.json';
$messages_file = 'messages.json';

$users = file_exists($users_file) ? json_decode(file_get_contents($users_file), true) : [];
$messages = file_exists($messages_file) ? json_decode(file_get_contents($messages_file), true) : [];

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

// Send message
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
            'timestamp' => date("Y-m-d H:i:s")
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
});</script>
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

    <form method="POST">
        <input type="text" name="receiver_code" placeholder="Enter Friend Code to Start Chat" required>
        <textarea name="message" placeholder="Type your message..." required></textarea>
        <button type="submit" name="send_message">Send</button>
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
                ?>
                <div class="message-bubble <?= $bubble_class ?>">
                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    <span class="timestamp"><?= htmlspecialchars($msg['sender_name']) ?> • <?= $msg['timestamp'] ?></span>
                </div>
                <div class="clearfix"></div>
            <?php endforeach; ?>
        <?php elseif ($chat_with): ?>
            <p>No messages yet. Say hi!</p>
        <?php else: ?>
            <p>Select a user to view your chat.</p>
        <?php endif; ?>
    </div>

    <div class="logout">
        <a href="?logout=1">Logout</a>
    </div></div>
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
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8a6 6 0 10-12 0v4a2 2 0 01-2 2h16a2 2 0 01-2-2v-4z"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
    <span>Messages</span>
  </a>
  <a href="/frontend/profile.html" class="nav-item" aria-label="Profile">
    <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0113 0"/></svg>
    <span>Profile</span>
  </a>
</nav>

<style>
  .bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0; right: 0;
    background: #fff;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-around;
    padding: 8px 0;
    box-shadow: 0 -1px 6px rgba(0,0,0,0.1);
    z-index: 1000;
  }

  .bottom-nav .nav-item {
    flex-grow: 1;
    text-align: center;
    font-size: 12px;
    color: #555;
    text-decoration: none;
    font-weight: 600;
    padding: 4px 0;
    transition: color 0.3s ease;
  }
  .bottom-nav .nav-item:hover,
  .bottom-nav .nav-item:focus {
    color: #0095f6;
  }

  .bottom-nav .nav-item .icon {
    width: 24px;
    height: 24px;
    display: block;
    margin: 0 auto 3px;
    stroke-linejoin: round;
    stroke-linecap: round;
  }
</style>

</body>
</html>
