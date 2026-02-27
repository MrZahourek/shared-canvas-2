class Song {
    constructor(scr, songName) {
        this.audio = new Audio();
        this.scr = scr;
        this.isLoaded = false;

        this.songName = songName;

        this.setup();
    }

    setup() {
        this.audio.src = this.scr;
        this.audio.oncanplaythrough = () => {this.isLoaded = true}
    }
}