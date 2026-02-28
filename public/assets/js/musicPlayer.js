// --- 1. Music Database ---
// Make sure to put your actual .mp3 files into a folder (e.g., public/assets/music/)
// and update the "file" paths below to match your local file names!
const tracks = [
    {
        title: "Track 01 - Unknown.mp3",
        file: "assets/audio/2 LOOP Dreamcore Ice Cream (by juanjo_sound).wav",
        image: "assets/images/music_2.jpg"
    },
    {
        title: "Track 02 - The Void.mp3",
        file: "assets/audio/8 LOOP Dreamcore Fragments (by juanjo_sound).wav",
        image: "assets/images/music_2.jpg"
    },
    {
        title: "Track 03 - Liminal.mp3",
        file: "assets/audio/Mandelbo - Music of a Lost Weekend - 05 Distant Life.mp3",
        image: "assets/images/music_1.jpg?v=2"
    }
];

// --- 2. State Variables ---
let currentTrackIndex = 0;
let isPlaying = false;
const audio = new Audio(); // The hidden HTML5 audio engine

// --- 3. DOM Elements ---
const playBtn = document.querySelector(".mb-play");
const pauseBtn = document.querySelector(".mb-pause");
const prevBtn = document.querySelector(".mb-prev");
const nextBtn = document.querySelector(".mb-next");
const volumeSlider = document.querySelector(".music_volume");

const trackInfoDisplay = document.getElementById("track_info");
const timeDisplay = document.querySelector(".music_display_time");
const visualiser = document.querySelector(".music_display_visualiser");
const albumArt = document.getElementById("music_album_art");

// --- 4. Functions ---
function loadTrack(index) {
    const track = tracks[index];
    audio.src = track.file;
    trackInfoDisplay.innerText = track.title;
    albumArt.src = track.image;
    timeDisplay.innerText = "00:00";
    visualiser.innerText = "■ ■ ■ □ □ □"; // Idle visualizer
}

function playTrack() {
    audio.play();
    isPlaying = true;
    visualiser.innerText = "■ ■ ▮ ▮ ■ ▮"; // Active visualizer
}

function pauseTrack() {
    audio.pause();
    isPlaying = false;
    visualiser.innerText = "■ ■ ■ □ □ □"; // Idle visualizer
}

function updateTime() {
    if (isNaN(audio.duration)) return;

    // Calculate minutes and seconds
    const currentMins = Math.floor(audio.currentTime / 60);
    const currentSecs = Math.floor(audio.currentTime % 60);

    // Add a leading zero if it's less than 10 (e.g., "0:5" becomes "00:05")
    const formattedTime =
        (currentMins < 10 ? "0" + currentMins : currentMins) + ":" +
        (currentSecs < 10 ? "0" + currentSecs : currentSecs);

    timeDisplay.innerText = formattedTime;
}

// --- 5. Event Listeners ---
playBtn.addEventListener("click", () => {
    if (!isPlaying) playTrack();
});

pauseBtn.addEventListener("click", () => {
    if (isPlaying) pauseTrack();
});

prevBtn.addEventListener("click", () => {
    currentTrackIndex--;
    if (currentTrackIndex < 0) currentTrackIndex = tracks.length - 1; // Loop to end
    loadTrack(currentTrackIndex);
    if (isPlaying) playTrack();
});

nextBtn.addEventListener("click", () => {
    currentTrackIndex++;
    if (currentTrackIndex >= tracks.length) currentTrackIndex = 0; // Loop to start
    loadTrack(currentTrackIndex);
    if (isPlaying) playTrack();
});

// Update volume when slider is dragged (HTML range is 0-100, Audio volume is 0.0-1.0)
volumeSlider.addEventListener("input", (e) => {
    audio.volume = e.target.value / 100;
});

// Auto-update the timer text every second
audio.addEventListener("timeupdate", updateTime);

// Auto-play the next song when current song finishes
audio.addEventListener("ended", () => {
    nextBtn.click();
});

// --- 6. Initialization on Load ---
window.addEventListener("load", () => {
    // Set initial volume to whatever the slider is set to in HTML
    audio.volume = volumeSlider.value / 100;
    loadTrack(currentTrackIndex);
});