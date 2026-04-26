var mouseInput;
var keyboardInput;
var mouseX = 0;
var playerX;
var keyDown;
var canMove = true;
let moveInterval;
var duration = 12;
var spawnTime = 1000;
var score = 0;
var seconds = 0;
var minutes = 0;
var gameInitialized = false;
var spawnInterval;
var timerInterval;
const elementList = new Set();
const foodList = ["apple", "pear", "milk", "knife", "poop"]

let player = document.getElementById("player");

let mouseInputBtn = document.getElementById('mouseInput');
let keyboardInputBtn = document.getElementById('keyboardInput');

if (mouseInputBtn) {
    mouseInputBtn.addEventListener('click', function() {
        keyboardInput = false;
        mouseInput = true;
        console.log('Mouse input enabled');
        movePlayerMouse(mouseX);
        player.style.display = 'block';
        init();
        track();
        timer();
    });
}

if (keyboardInputBtn) {
    keyboardInputBtn.addEventListener('click', function() {
        keyboardInput = true;
        mouseInput = false;
        movePlayerMouse(0);
        playerX = 0;
        player.style.display = 'block';
        init();
        track();
        timer();
    });
}

document.addEventListener('mousemove', (event) => {
    mouseX = event.clientX;
    if(mouseInput) {
        movePlayerMouse(mouseX);
    }
});   
document.addEventListener('keydown', (event) => {
    if(keyboardInput) {
        console.log("Key pressed: " + event.key);
        if (event.key === "ArrowRight" && !keyDown){
            keyDown = true;
            moveInterval = setInterval(() => {
                movePlayerKeyboard(10);
            }, 20);
        }
        if (event.key === "ArrowLeft" && !keyDown){
            keyDown = true;
            moveInterval = setInterval(() => {
                movePlayerKeyboard(-10);
            }, 20);
        }
    }
});
document.addEventListener("keyup", (event) =>{
    if (event.key === "ArrowRight" || "ArrowLeft"){
        keyDown = false;
        clearInterval(moveInterval);
    }
})
function movePlayerMouse(x){
    x = x - player.offsetWidth / 2;
    player.style.transform = `translateX(${x}px)`;
}
function movePlayerKeyboard(x){
    var newX = playerX + x;
    // Clamp the position between 0 and window width minus player width
    var maxX = window.innerWidth - player.offsetWidth;
    newX = Math.max(0, Math.min(newX, maxX));
    player.style.transform = `translateX(${newX}px)`;
    playerX = newX;
}
function spawnElement() {
    var bad = false;
    const food = Math.floor(Math.random() * 5)
    if (food > 2){
        bad = true;
    }
    const template = document.getElementById(foodList[food]);
    const el = template.cloneNode(true);
    el.bad = bad;

  el.style.display = 'block';
  el.id = ''; // prevent duplicate IDs

  el.style.left = Math.random() * window.innerWidth + 'px';

  el.style.animationDuration = duration + 's';

  document.body.appendChild(el);

  elementList.add(el);

  el.fallingTimeout = setTimeout(() => {
    if (document.body.contains(el)) {
        if(!bad){
            gameOver();
        }
        el.remove();
    }
  }, duration * 1000);
}
function init(){
    if (gameInitialized) {
        return; // Don't reinitialize if already running
    }
    gameInitialized = true;
    
    spawnInterval = setInterval(() => {
        spawnElement();
    }, spawnTime);
}
function track() {
  elementList.forEach(el => {
    const rect = el.getBoundingClientRect();
    const x = rect.left;
    const y = rect.top;
    const playerRect = player.getBoundingClientRect();

    if (rect.right >= playerRect.left && rect.left <= playerRect.right &&
        rect.bottom >= playerRect.top && rect.top <= playerRect.bottom) {
        console.log("Collision detected!");
        el.remove();
        if(el.bad){
            gameOver();
        }
        score = score + 1;
        document.getElementById('score').textContent = 'Score: ' + score;
        elementList.delete(el); 
    }

  });
  requestAnimationFrame(track);
}
function timer(){
    timerInterval = setInterval(() => {
        seconds = seconds + 1;
        if(seconds >= 60){
            seconds = 0;
            minutes = minutes + 1;
        }
        var timeString;
            if(minutes < 10){
                timeString = "0" + minutes;
            }
            else{
                timeString = minutes;
            }
            timeString = timeString + ":";
            if(seconds < 10){
                timeString = timeString + "0" + seconds;
            }
            else{
                timeString = timeString + seconds;
            }
            document.getElementById('timer').textContent = timeString;
            duration = duration - 0.1;
            console.log("fall duration: " + duration);
    }, 1000);
}
function gameOver() {
    console.log("dead");

    const userId = document.getElementById("userId").textContent;
    const finalTime = (minutes * 60) + seconds;

    console.log("Sending score:", score, "Time:", finalTime, "UserId:", userId);

    fetch('/scores', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            user_id: parseInt(userId, 10),
            score: score,
            time: finalTime
        })
    })
    .then(response => {
        console.log("Response status:", response.status);
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data);
        // Redirect to gameover page with score and time in URL
        window.location.href = `/minigame/gameover?score=${data.score}&time=${data.time}`;
    })
    .catch(err => {
        console.error("Error saving score:", err);
        window.location.href = "/minigame/gameover";
    });
}
