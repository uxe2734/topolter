<?php
require_once '../init.php';
if(!isset($_SESSION['user_id'])) header("Location: login.php");
$myId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Chat | Topolter Messenger</title>
<link rel="icon" href="../logo.png">
<link rel="stylesheet" href="../assets/style.css">

</head>
<body class="bg-gray-100 h-screen flex flex-col md:flex-row">

<div class="header">
    <span class="font-bold">
        <img src="../logo.png" id="logo"> Topolter
    </span>
    <br>
    <button id="logoutBtn" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
        Logout
    </button>
</div>

<div class="container">

<!-- Users list -->
<div id="userSection" class="users">
    <input type="text" id="searchUser" placeholder="Search...">
    <div id="userList" class="user-list"></div>
</div>

<!-- Chat section -->
<div id="chatSection" class="chat">

    <div class="chat-header" id="chatHeader" style="display:none;">

        <button id="backBtn" class="back-btn">→</button>

        <div class="chat-center">
            <div class="chat-name" id="chatTitle"></div>
            <div class="chat-avatar" id="chatAvatar">A</div>
        </div>

    </div>

    <div id="chatBox" class="chat-box"></div>

    <button id="scrollBtn">⬇</button>

    <div id="uploadProgressContainer" style="display:none; padding:10px;">
        <div style="background:#e5e7eb; width:100%; height:8px; border-radius:4px;">
            <div id="uploadBar" style="
                width:0%;
                height:8px;
                background:linear-gradient(90deg,#4f46e5,#3b82f6);
                border-radius:4px;
                transition:0.2s;">
            </div>
        </div>
        <div id="uploadPercent" style="font-size:12px; margin-top:5px; text-align:center;"></div>
    </div>

    <div class="chat-input">

        <button id="sendBtn" class="send-btn">
            <span>Send</span>
        </button>

        <input type="file" id="fileInput" style="display:none;">
        <button onclick="document.getElementById('fileInput').click()">📎</button>

        <input type="text" id="messageInput"
               class="flex-1 p-2 border rounded"
               placeholder="Message...">

    </div>

</div>
</div>
<script>
    let selectedUser=null;
    let unreadMap = {};
    let lastNotificationTime = 0;

    Notification.requestPermission();


    function escapeHTML(text){
        const div=document.createElement("div");
        div.textContent=text;
        return div.innerHTML;
    }

    function timeAgo(ts){
        const diff = Math.floor((Date.now() - new Date(ts))/1000);
        if(diff<60) return diff+" seconds ago";
        if(diff<3600) return Math.floor(diff/60)+" minutes ago";
        if(diff<86400) return Math.floor(diff/3600)+" hours ago";
        return Math.floor(diff/86400)+" days ago";
    }

    // ================= Users list =================
    function renderUsers(users){
        const list = document.getElementById("userList");
        list.innerHTML="";
        users.forEach(u=>{
            const count = unreadMap[u.id] || 0;
            const div=document.createElement("div");
            div.className="user";

            if(u.display_name=="<?= $_SESSION['display_name'] ?>"){
                u.display_name="<?= $_SESSION['display_name'] ?>"+" (Saved messages)";
            }

            const initials = u.display_name.substring(0,2);

            div.innerHTML = `
                <div style="display:flex;align-items:center;width:100%;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar">${initials}</div>
                        <div class="user-info">
                            <div class="user-name">${u.display_name}</div>
                            <div class="user-last">Click to view messages</div>
                        </div>
                    </div>
                    ${count > 0 ? `<div class="badge">${count}</div>` : ""}
                </div>
            `;
            
            div.onclick = ()=>{
                selectedUser = u.id;

                document.getElementById("chatHeader").style.display = "flex";
                history.pushState({chatOpen:true}, "");
                document.getElementById("chatAvatar").textContent = u.display_name.substring(0,2);
                loadMessages();
                setTimeout(()=>{
                    const box = document.getElementById("chatBox");
                    box.scrollTop = box.scrollHeight;
                }, 100);
                
                // Mark as read
                fetch("../api/mark_read.php", {
                    method:"POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body:"user_id=" + selectedUser
                });

                // Mobile
                if(window.innerWidth < 768){
                    document.getElementById("userSection").style.display="none";
                    document.getElementById("chatSection").classList.add("active");
                }

                // highlight
                document.querySelectorAll("#userList div").forEach(d=>d.classList.remove("active"));
                div.classList.add("active");

                document.getElementById("chatTitle").textContent = u.display_name;
            }
            list.appendChild(div);
        });
    }

    function loadConversations(){
        fetch("../api/get_conversations.php")
        .then(r=>r.json())
        .then(renderUsers);
    }
    loadConversations();

    document.getElementById("searchUser").addEventListener("input", e=>{
        const q = e.target.value;
        if(q.length<1) return loadConversations();
        fetch("../api/search_user.php?q="+encodeURIComponent(q))
        .then(r=>r.json())
        .then(renderUsers);
    });

    // ================= Send message =================
    document.getElementById("sendBtn").addEventListener("click", ()=>{

        const msg = document.getElementById("messageInput").value.trim();
        if(!selectedUser || !msg) return;

        const fd = new FormData();
        fd.append("receiver_id",selectedUser);
        fd.append("message",msg);

        fetch("../api/send_message.php",{method:"POST",body:fd, credentials:"same-origin"})
        .then(r=>r.json())
        .then(res=>{
            if(res.status==="ok"){
                document.getElementById("messageInput").value="";
                loadMessages();
                setTimeout(()=>{
                    const box = document.getElementById("chatBox");
                    box.scrollTop = box.scrollHeight;
                }, 100);
            } else alert(res.error);
        });
    });

    // ================= Delete message =================
    function deleteMessage(msgId){
        if(!confirm("Do you want to delete this message?")) return;

        const fd = new FormData();
        fd.append("message_id", msgId);

        fetch("../api/delete_message.php",{method:"POST",body:fd, credentials:"same-origin"})
        .then(r=>r.json())
        .then(res=>{
            if(res.status==="ok") loadMessages();
            else alert(res.error);
        });
    }

    // ================= Load messages =================
    function loadMessages(){
        const box = document.getElementById("chatBox");
        const isNearBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 50;

        if(isNearBottom){
            box.scrollTop = box.scrollHeight;
        }
        if(!selectedUser) return;

        fetch(`../api/get_messages.php?user_id=${selectedUser}`, {credentials:"same-origin"})
        .then(r=>r.json())
        .then(msgs=>{
            const box = document.getElementById("chatBox");
            box.innerHTML="";

            msgs.forEach(m=>{
                const div = document.createElement("div");
                let content = "";

                if(m.file_path){
                    const ext = m.file_type.toLowerCase();

                    if(['jpg','jpeg','png','gif','webp'].includes(ext)){
                        content = `<img src="../uploads/${m.file_path}" style="max-width:200px;border-radius:10px;">`;
                    } 
                    else if(['mp4','webm','ogg'].includes(ext)){
                        content = `<video controls style="max-width:200px;">
                                    <source src="../uploads/${m.file_path}">
                                </video>`;
                    } 
                    else if(['wav','mp3'].includes(ext)){
                        content = `<audio controls style="max-width:200px;">
                                    <source src="../uploads/${m.file_path}">
                                </audio>`;
                    } 
                    else {
                        const fileUrl = encodeURI("../uploads/" + m.file_path);
                        content = `<a href="${fileUrl}" target="_blank">📎 Download file</a>`;
                    }
                } else {
                    content = escapeHTML(m.message);
                }

                div.innerHTML = `
                ${content}
                <div class="time">
                ${m.time}
                <strong><span class="delete-btn" onclick="deleteMessage(${m.id})">Delete</span></strong>
                </div>`;

                if(m.sender_id === <?= $myId ?>){
                    div.className="message me";
                } else {
                    div.className="message other";
                }

                box.appendChild(div);
            });
        });
    }

    function loadUnread(){
        fetch("../api/get_unread_per_user.php")
        .then(r=>r.json())
        .then(data=>{
            unreadMap = {};

            if(data.length > 0){
                let total = data.reduce((sum,u)=>sum + parseInt(u.count),0);

                let now = Date.now();

                // If there are messages and at least 1 minute passed
                if(total > 0 && (now - lastNotificationTime > 60000)){
                    new Notification("New message 📩", {
                        body: `You have ${total} new messages`
                    });

                    lastNotificationTime = now;
                }

                lastTotal = total;
            }

            data.forEach(u=>{
                unreadMap[u.sender_id] = u.count;
            });

            loadConversations();
        });
    }

    // ================= polling every 3 seconds =================
    setInterval(()=>{ loadMessages(); },3000);
    setInterval(()=>{ loadUnread(); },3000);

    document.getElementById("logoutBtn").addEventListener("click", ()=>{
        if(!confirm("Are you sure you want to logout?")) return;

        fetch("../api/logout.php",{
            method:"POST",
            credentials:"same-origin"
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.status==="ok"){
                window.location.href="login.php";
            }
        });
    });

    document.getElementById("fileInput").addEventListener("change", ()=>{
        const file = document.getElementById("fileInput").files[0];
        if(!file || !selectedUser) return;

        const fd = new FormData();
        fd.append("receiver_id", selectedUser);
        fd.append("file", file);

        // Show progress bar
        document.getElementById("uploadProgressContainer").style.display = "block";
        document.getElementById("uploadBar").style.width = "0%";
        document.getElementById("uploadPercent").innerText = "0%";

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "../api/send_file.php", true);

        // Upload progress
        xhr.upload.onprogress = (e)=>{
            if(e.lengthComputable){
                let percent = Math.round((e.loaded / e.total) * 100);
                document.getElementById("uploadBar").style.width = percent + "%";
                document.getElementById("uploadPercent").innerText = percent + "%";
            }
        };

        // Finish upload
        xhr.onload = ()=>{
            document.getElementById("fileInput").value = "";

            setTimeout(()=>{
                document.getElementById("uploadProgressContainer").style.display = "none";
            }, 500);

            let res = JSON.parse(xhr.responseText);
            if(res.status === "ok"){
                loadMessages();
            } else {
                alert(res.error);
            }
        };

        xhr.onerror = ()=>{
            alert("Connection error!");
        };

        xhr.send(fd);
    });

    document.getElementById("backBtn").addEventListener("click", ()=>{
        history.back();
        document.getElementById("chatHeader").style.display = "none";
        document.getElementById("chatSection").classList.remove("active");
        document.getElementById("userSection").style.display="flex";
        selectedUser = null;
    });

    document.getElementById("messageInput").addEventListener("keypress", function(e){
        if(e.key === "Enter"){
            e.preventDefault();
            document.getElementById("sendBtn").click();
            setTimeout(()=>{
                const box = document.getElementById("chatBox");
                box.scrollTop = box.scrollHeight;
            }, 100);
        }
    });

    const box = document.getElementById("chatBox");
    const scrollBtn = document.getElementById("scrollBtn");

    box.addEventListener("scroll", ()=>{
        const distanceFromBottom = box.scrollHeight - box.scrollTop - box.clientHeight;

        if(distanceFromBottom > 150){
            scrollBtn.style.display = "block";
        } else {
            scrollBtn.style.display = "none";
        }
    });

    scrollBtn.addEventListener("click", ()=>{
        box.scrollTo({
        top: box.scrollHeight,
        behavior: "smooth"
    });
    });

    document.getElementById("sendBtn").addEventListener("click", function(e){
        const button = this;

        const rect = button.getBoundingClientRect();

        const circle = document.createElement("span");
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${e.clientX - rect.left - radius}px`;
        circle.style.top = `${e.clientY - rect.top - radius}px`;
        circle.classList.add("ripple");

        const old = button.querySelector(".ripple");
        if(old) old.remove();

        button.appendChild(circle);
    });

    window.addEventListener("popstate", function(e){
        if(selectedUser){
            document.getElementById("chatHeader").style.display = "none";
            document.getElementById("chatSection").classList.remove("active");
            document.getElementById("userSection").style.display="flex";

            selectedUser = null;

            history.pushState(null, "");
        }
    });

</script>
</body>
</html>