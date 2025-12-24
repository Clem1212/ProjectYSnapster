<html lang="en"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Snapster</title>
    <link rel="icon" type="image/x-icon" href="/image/logo-removebg-preview.png">
    <link rel="stylesheet" href="/frontend/app.js">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
       <link rel="stylesheet" href="/frontend/style.css">

  <style>
    /* Modern Social Media App - Feed Page */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: #fafafa;
      color: #262626;
      line-height: 1.5;
      padding-bottom: 60px; /* Space for mobile nav */
    }
    
   /* theme.css */
.instagram-theme {
  --bg-color: #fafafa;
  --card-bg: #ffffff;
  --text-color: #262626;
  --text-secondary: #8e8e8e;
  --border-color: #dbdbdb;
  --primary-color: #0095f6;
  --like-color: #ed4956;
  --header-bg: #ffffff;
}

.bereal-theme {
  --bg-color: #000000;
  --card-bg: #121212;
  --text-color: #ffffff;
  --text-secondary: #a8a8a8;
  --border-color: #333333;
  --primary-color: #FF375F;
  --like-color: #FF375F;
  --header-bg: #000000;
}

body {
  background-color: var(--bg-color);
  color: var(--text-color);
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
    
    /* Feed posts */
    .feed-container {
      max-width: 600px;
      margin: 0 auto;
      padding-top: 20px;
    }
    
    .post {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 8px;
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .post-header {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border-color);
    }
    
    .post-user-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: #efefef;
      margin-right: 10px;
      overflow: hidden;
    }
    
    .post-user-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .post-user-info {
      flex-grow: 1;
    }
    
    .post-user-name {
      font-weight: 600;
      font-size: 14px;
      color: var(--text-color);
    }
    
    .post-time {
      font-size: 12px;
      color: var(--text-secondary);
    }
    
    .post-more {
      color: var(--text-color);
      font-size: 20px;
      cursor: pointer;
    }
    
    .post-image-container {
      position: relative;
      width: 100%;
      background-color: #000;
      display: flex;
      justify-content: center;
    }
    
    .post-image {
      width: 100%;
      display: block;
      max-height: 600px;
      object-fit: contain;
    }
    
    .retake-badge {
      position: absolute;
      bottom: 10px;
      right: 10px;
      background-color: rgba(0, 0, 0, 0.7);
      color: white;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 500;
    }
    
    .post-actions {
      display: flex;
      padding: 8px 16px;
      border-top: 1px solid var(--border-color);
    }
    
    .post-action {
      margin-right: 16px;
      color: var(--text-color);
      font-size: 24px;
      cursor: pointer;
      background: none;
      border: none;
      display: flex;
      align-items: center;
      padding: 0;
    }
    
    .post-action.liked {
      color: var(--like-color);
    }
    
    .post-action span {
      font-size: 14px;
      margin-left: 6px;
      font-weight: 500;
    }
    
    .post-caption {
      padding: 12px 16px;
      font-size: 14px;
      color: var(--text-color);
    }
    
    .post-caption-username {
      font-weight: 600;
      margin-right: 5px;
    }
    
    .comment-section {
      padding: 0 16px 16px;
      border-top: 1px solid var(--border-color);
    }
    
    .comment-header {
      padding: 12px 0;
      font-weight: 600;
      font-size: 14px;
      color: var(--text-color);
    }
    
    .comment {
      padding: 6px 0;
      font-size: 14px;
      color: var(--text-color);
    }
    
    .comment-username {
      font-weight: 600;
      margin-right: 4px;
    }
    
    .add-comment {
      display: flex;
      padding-top: 12px;
      align-items: center;
    }
    
    .comment-input {
      flex: 1;
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 8px 16px;
      background-color: transparent;
      color: var(--text-color);
      font-size: 14px;
    }
    
    .comment-input:focus {
      outline: none;
      border-color: var(--text-secondary);
    }
    
    .comment-button {
      margin-left: 8px;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 24px;
      padding: 8px 16px;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
    }
    
    /* BeReal notification */
    .bereal-notification {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background-color: var(--primary-color);
      color: white;
      padding: 12px 16px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      z-index: 1000;
      max-width: 90%;
    }
    
    .notification-icon {
      font-size: 18px;
      margin-right: 8px;
    }
    
    .notification-text {
      font-weight: 600;
    }
    
    .countdown {
      margin-left: 8px;
      font-weight: 700;
      color: white;
    }
    
    /* Bottom Navigation */
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      display: flex;
      justify-content: space-around;
      background-color: var(--header-bg);
      border-top: 1px solid var(--border-color);
      padding: 10px 0;
      z-index: 100;
    }
    
    .nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      color: var(--text-color);
      text-decoration: none;
      font-size: 12px;
    }
    
    .nav-icon {
      font-size: 24px;
      margin-bottom: 4px;
    }
    
    /* Page specific elements */
    .page-title {
      font-size: 20px;
      font-weight: 600;
      color: var(--text-color);
      padding: 16px;
      text-align: center;
    }
    
    .action-link {
      display: inline-block;
      background-color: var(--primary-color);
      color: white;
      padding: 8px 16px;
      border-radius: 24px;
      text-decoration: none;
      font-weight: 600;
      margin-bottom: 20px;
      transition: background-color 0.2s;
    }
    
    .action-link:hover {
      background-color: var(--primary-color);
      opacity: 0.9;
    }
    
    .logout-btn {
      position: fixed;
      top: 10px;
      right: 10px;
      background-color: transparent;
      color: var(--text-color);
      border: none;
      font-size: 16px;
      cursor: pointer;
      z-index: 110;
    }
    
    /* Toggle theme button */
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
    
    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-secondary);
    }
    
    .empty-icon {
      font-size: 48px;
      margin-bottom: 16px;
    }
    
    .empty-text {
      font-size: 16px;
      margin-bottom: 24px;
    }
    
    /* Loading spinner */
    .loading {
      display: flex;
      justify-content: center;
      padding: 40px 0;
    }
    
    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid rgba(0, 0, 0, 0.1);
      border-radius: 50%;
      border-left-color: var(--primary-color);
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Media queries */
    @media (min-width: 768px) {
      .bottom-nav {
        display: none;
      }
      
      body {
        padding-bottom: 0;
      }
    }
    


   .fa-magnifying-glass:hover  {
  font-size: 25px;
   }

  </style>
  <style>
    .theme-switch {
  cursor: pointer;
  border: none;
  background: none;
  font-size: 20px;
  z-index: 10;
  padding: 5px;
}body.bereal-theme {
  background-color: #000000;
  color: #333;
}

/* BeReal input */
.bereal-theme input {
  border: 1px solid #ddd;
  background-color: #fff;
}

.bereal-theme input:focus {
  border-color: #000;
}

/* BeReal button */
.bereal-theme button {
  background-color:--text-color;
  color: white;
}

.bereal-theme button:hover {
  background-color: #333;
}body.instagram-theme {
  background-color: #fafafa;
  color: #262626;
}

/* Instagram input */
.instagram-theme input {
  border: 1px solid #dbdbdb;
  background-color: #fafafa;
}

.instagram-theme input:focus {
  border-color: #a8a8a8;
}

/* Instagram button */
.instagram-theme button {
  background-color: --text-color;
  color: white;
}

.search-page-container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px 15px;
}

.search-bar-wrapper {
  margin-bottom: 20px;
}

.search-input-inner {
  position: relative;
  display: flex;
  align-items: center;
  background-color: var(--input-bg); /* Use your light gray variable */
  border-radius: 10px;
  padding: 0 12px;
  border: 1px solid var(--border-color);
}

.search-icon-left {
  color: var(--text-secondary);
  font-size: 14px;
}

.search-input-inner input {
  width: 100%;
  background: transparent;
  border: none;
  padding: 10px 10px;
  font-size: 16px;
  color: var(--text-color);
  outline: none;
}

/* Polishing the Results Area */
.search-results-container {
  display: flex;
  flex-direction: column;
}

/* User Card Style for Results */
.search-result-user {
  display: flex;
  align-items: center;
  padding: 12px 0;
  text-decoration: none;
  border-bottom: 1px solid rgba(var(--border-color), 0.3);
}

.search-result-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #eee;
  margin-right: 12px;
  object-fit: cover;
}

.search-result-info {
  display: flex;
  flex-direction: column;
}

.search-result-username {
  font-weight: 600;
  color: var(--text-color);
  font-size: 14px;
}

.search-result-name {
  color: var(--text-secondary);
  font-size: 14px;
}





/* Container with significant padding and gap */
.search-results {
  display: grid;
  /* Larger min-width makes images bigger and reduces columns */
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
  gap: 40px; /* Massive space between results for a premium feel */
  padding: 40px 20px;
  max-width: 100px;
  margin: 0 auto;
}

/* Polished Large Card */
.polished-search-card {
  background-color: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: 10px; /* Large rounded corners like Gemini/iOS */
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
  cursor: pointer;
  display: flex;
  flex-direction: column;
  margin:10px
}

.polished-search-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  border-color: var(--primary-color);
}

/* Image that covers the top half/majority of the card */
.card-image-wrapper {
  width: 100%;
  aspect-ratio: 16 / 9; /* Cinematic wide aspect ratio */
  overflow: hidden;
  background-color: #f0f0f0;
}

.card-img-cover {
  width: 100%;
  height: 100%;
  object-fit: cover; /* Ensures the image fills the space completely */
  transition: transform 0.6s ease;
}

.polished-search-card:hover .card-img-cover {
  transform: scale(1.08);
}

/* Text area with generous padding */
.card-content {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.card-user-row {
  display: flex;
  align-items: center;
  gap: 40px;
}

.card-username {
  font-weight: 800;
  font-size: 18px; /* Bigger text */
  color: var(--text-color);
  letter-spacing: -0.5px;
}

.card-caption {
  font-size: 15px;
  color: var(--text-secondary);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
  </style>
</head>
<body >

  <div class="app-header">
    <div class="header-content">
      <div class="logo">  <img id="logo-img" src="/image/border2.png" style="width: 120px; height: auto;">
</div>
      <div class="theme-switch" id="themeSwitch"><i class="fa-solid fa-sun theme-icon"></i></div>
      <!-- icons-->
      <div class="header-icons">
        <a href="/frontend/profile.html" class="nav-item">
  <i class="fa-solid fa-user header-icon"></i>
        </a>
      
         <a href="/frontend/chat_app.php" class="nav-item">
        <i class="fa-solid fa-message" style="font-size: 20px;"></i>
        </a>
          <a href="/frontend/search.php" class="nav-item">
       <i class="fa-solid fa-magnifying-glass"style="font-size: 20px;"></i>
        </a>
      </div>
    </div>
  </div>
  
  <div class="container">
    <button id="logoutBtn" class="logout-btn" >
      <i class="fa-solid fa-right-from-bracket" style="font-size: 25px;"></i>
    </button>
    
   
    <!--<img id="logo-img" src="/image/border2.png" style="width: 200px; height: auto;">-->
   
  <br>
 <div class="search-page-container">
  <div class="search-bar-wrapper">
    <div class="search-input-inner">
      <i class="fa-solid fa-magnifying-glass search-icon-left"></i>
      <input type="text" id="searchInput" placeholder="Search" onkeyup="handleSearchKeyUp(event)">
    </div>
  </div>

  <div class="search-results-container" id="searchResults">
    <div class="search-placeholder">
     
      <p>Search for friends or posts</p>
    </div>
  </div>
</div>
  <script>
    function handleSearchKeyUp(event) {
    // Trigger search on Enter key
    if (event.key === "Enter") {
        performSearch();
    }
    
    // Optional: Real-time search
    // if (event.target.value.length > 2) performSearch();
}

function performSearch() {
    const query = document.getElementById('searchInput').value;
    const resultsContainer = document.getElementById('searchResults');
    
    if (!query) return;

    // Show a loading state
    resultsContainer.innerHTML = '<div class="loader">Searching...</div>';

    // When you fetch your PHP data, format the result like this:
    /*
    resultsContainer.innerHTML = `
      <div class="search-result-user">
        <img src="uploads/profile1.jpg" class="search-result-avatar">
        <div class="search-result-info">
          <span class="search-result-username">username_here</span>
          <span class="search-result-name">Display Name</span>
        </div>
      </div>
    `;
    */
}
      document.getElementById('logoutBtn').addEventListener('click', () => {
      window.location.href = '../frontend/index.html';
    });
    async function performSearch() {
      const query = document.getElementById('searchInput').value.toLowerCase();
      const resultsDiv = document.getElementById('searchResults');
      resultsDiv.innerHTML = '<p>Searching...</p>';

      try {
        const response = await fetch('../backend/feed.php');
        const data = await response.json();

        const filtered = data.filter(post => {
          const caption = post.caption?.toLowerCase() || '';
          const username = post.username?.toLowerCase() || '';
          const name = post.name?.toLowerCase() || '';
          const comments = post.comments?.join(' ').toLowerCase() || '';
          return (
            caption.includes(query) ||
            username.includes(query) ||
            name.includes(query) ||
            comments.includes(query)
          );
        });

        if (filtered.length === 0) {
          resultsDiv.innerHTML = '<p>No results found.</p>';
          return;
        }
//resultsDiv.innerHTML1
resultsDiv.innerHTML = filtered.map(post => `
  <div class="polished-search-card" onclick="window.location.href='feed.html'">
    <div class="card-image-wrapper">
      <img src="../backend/${post.image}" class="card-img-cover" loading="lazy">
    </div>
    <div class="card-content">
      <div class="card-user-row">
        <span class="card-username">@${post.username}</span>
      </div>
      <p class="card-caption">${post.caption || 'No caption provided.'}</p>
    </div>
  </div>
`).join('');

      } catch (err) {
        console.error('Search error:', err);
        resultsDiv.innerHTML = '<p>Error loading results. Try again later.</p>';
      }
    }
  </script>


<div id="myOverlay" class="overlay" style="display: none;">
  <span class="closebtn" onclick="closeSearch()" title="Close Overlay">×</span>
  <div class="overlay-content">
    

    <form>
      <input type="text" placeholder="Search.." name="search" id="searchInput">
      <button type="submit"><i class="fa fa-search"></i></button>
  
      <ul id="results">
      


    

    <script>
      const searchInput = document.getElementById("searchInput");

      // store name elements in array-like object
      const namesFromDOM = document.getElementsByClassName("name");
      
      // listen for user events
      searchInput.addEventListener("keyup", (event) => {
          const { value } = event.target;
          
          // get user search input converted to lowercase
          const searchQuery = value.toLowerCase();
          
          for (const nameElement of namesFromDOM) {
              // store name text and convert to lowercase
              let name = nameElement.textContent.toLowerCase();
              
              // compare current name to search input
              if (name.includes(searchQuery)) {
                  // found name matching search, display it
                  nameElement.style.display = "block";
              } else {
                  // no match, don't display name
                  nameElement.style.display = "none";
              }
          }
      });
      
      
      
      </script>


  </ul></form></div>
  
 
</div>

  
         


       


  
  
  
  <div class="bottom-nav">
    <a href="feed.html" class="nav-item">
      <i class="fa-solid fa-home nav-icon"></i>
      <span>Home</span>
    </a>
    <a href="/frontend/search.php" class="nav-item">
      <i class="fa-solid fa-magnifying-glass nav-icon"></i>
      <span>Search</span>
    </a>
    <a href="upload.html" class="nav-item">
      <i class="fa-solid fa-square-plus nav-icon"></i>
      <span>Upload</span>
    </a>
    <a href="chat_app.php" class="nav-item">
      <i class="fa-solid fa-bell nav-icon"></i>
      <span>Messages</span>
    </a>
    <a href="/frontend/profile.html" class="nav-item">
      <i class="fa-solid fa-user nav-icon"></i>
      <span>Profile</span>
    </a>
  </div>
<style>

</style>
<script>
  window.addEventListener('DOMContentLoaded', () => {
  // Theme switcher setup
  const savedTheme = localStorage.getItem('theme');
  const body = document.body;
  const themeSwitch = document.getElementById('themeSwitch');
  const logoImg = document.getElementById('logo-img');

  if (savedTheme === 'instagram') {
    body.classList.add('instagram-theme');
    if (themeSwitch) themeSwitch.innerHTML = '<i class="fa-solid fa-moon theme-icon"></i>';
    if (logoImg) logoImg.src = '/image/border1.png';
  } else {
    body.classList.add('bereal-theme');
    if (themeSwitch) themeSwitch.innerHTML = '<i class="fa-solid fa-sun theme-icon"></i>';
    if (logoImg) logoImg.src = '/image/border2.png';
  }

  if (themeSwitch) {
    themeSwitch.addEventListener('click', () => {
      if (body.classList.contains('bereal-theme')) {
        body.classList.replace('bereal-theme', 'instagram-theme');
        themeSwitch.innerHTML = '<i class="fa-solid fa-moon theme-icon"></i>';
        if (logoImg) logoImg.src = '/image/border1.png';
        localStorage.setItem('theme', 'instagram');
      } else {
        body.classList.replace('instagram-theme', 'bereal-theme');
        themeSwitch.innerHTML = '<i class="fa-solid fa-sun theme-icon"></i>';
        if (logoImg) logoImg.src = '/image/border2.png';
        localStorage.setItem('theme', 'bereal');
      }
    });
  }


  // BeReal Notification System
  let berealActive = false;
  let nextBeRealTime = null;
  let notificationElement = null;

  function scheduleNextBeReal() {
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    const hour = Math.floor(Math.random() * (22 - 8 + 1)) + 8;
    const minute = Math.floor(Math.random() * 60);

    nextBeRealTime = new Date(today.getTime());
    nextBeRealTime.setHours(hour, minute, 0, 0);

    if (nextBeRealTime <= now) {
      nextBeRealTime.setDate(nextBeRealTime.getDate() + 1);
    }
    console.log("Next BeReal notification at:", nextBeRealTime);
  }

  function showBeRealNotification() {
    if (notificationElement) {
      notificationElement.remove();
    }
    notificationElement = document.createElement('div');
    notificationElement.className = 'bereal-notification';
    notificationElement.innerHTML = `
      <i class="fa-solid fa-camera notification-icon"></i>
      <span class="notification-text">It's time to Snapster!</span>
      <span class="countdown" id="bereal-countdown">2:00</span>
    `;
    document.body.appendChild(notificationElement);

    let seconds = 120;
    const countdownElement = document.getElementById('bereal-countdown');

    const countdownInterval = setInterval(() => {
      seconds--;
      const minutes = Math.floor(seconds / 60);
      const secs = seconds % 60;
      if (countdownElement) countdownElement.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;

      if (seconds <= 0) {
        clearInterval(countdownInterval);
        if (notificationElement) {
          notificationElement.remove();
          notificationElement = null;
        }
        berealActive = false;
        scheduleNextBeReal();
      }
    }, 1000);
  }

  function startBeRealCycle() {
    scheduleNextBeReal();

    setInterval(() => {
      if (!berealActive) {
        const now = new Date();
        if (now >= nextBeRealTime) {
          berealActive = true;
          showBeRealNotification();
        }
      }
    }, 1000);
  }

  startBeRealCycle();

  
});


</script>

  


  <style>
   
    .search-box {
      max-width: 600px;
      margin: auto;
      display: flex;
      gap: 10px;
      background-color:var(--bg-color); 
       color: var(--text-color);
    }
    .search-box input {
      flex: 1;
      padding: 10px;
      font-size: 16px;
      
    }.search-results {
  max-width: 600px;
  margin: 40px auto;
  background-color: var(--bg-color);
  color: var(--text-color);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(56, 55, 55, 0.1);
  padding: 25px 30px;
  text-align: left;
  font-family: 'Segoe UI', sans-serif;
}
.search-result:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    transition: all 0.2s ease;
}
.search-input {
    width: 100%;
    padding: 0.6rem 1rem;
    border-radius: 12px;
    border: 1px solid #ddd;
    margin-bottom: 1rem;
}
.search-input:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0,123,255,0.3);
}

.result-item {
  padding: 15px 0;
  border-bottom: 1px solid rgba(130, 129, 129, 0.1);
  transition: background-color 0.3s ease;
}

.result-item:hover {
  background-color: rgba(134, 134, 134, 0.05); /* subtle hover effect */
}

.result-item:last-child {
  border-bottom: none;
}

.result-caption {
  font-weight: 600;
  font-size: 1.1em;
  margin-bottom: 4px;
  display: block;
}

.result-description {
  font-size: 0.95em;
  opacity: 0.8;
}

  </style>
</body></html>