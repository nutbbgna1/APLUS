// ============================================================
// LinguaMax — Text-to-Speech (Web Speech API)
// ============================================================

const TTS = {
    synth: window.speechSynthesis,
    speaking: false,

    speak(text, lang = 'en-US') {
        if (!this.synth) return;
        this.synth.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = lang;
        utterance.rate = 0.85;
        utterance.pitch = 1.0;

        // Try to find a good English voice
        const voices = this.synth.getVoices();
        const preferred = voices.find(v => v.lang === 'en-US' && v.name.includes('Samantha')) ||
                          voices.find(v => v.lang === 'en-US') ||
                          voices.find(v => v.lang.startsWith('en'));
        if (preferred) utterance.voice = preferred;

        // Animation feedback
        const btns = document.querySelectorAll('.speak-btn');
        utterance.onstart = () => {
            this.speaking = true;
            btns.forEach(b => b.classList.add('speaking'));
        };
        utterance.onend = () => {
            this.speaking = false;
            btns.forEach(b => b.classList.remove('speaking'));
        };

        this.synth.speak(utterance);
    },

    speakSlow(text) {
        if (!this.synth) return;
        this.synth.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 0.55;
        utterance.pitch = 1.0;

        const voices = this.synth.getVoices();
        const preferred = voices.find(v => v.lang === 'en-US') || voices.find(v => v.lang.startsWith('en'));
        if (preferred) utterance.voice = preferred;

        this.synth.speak(utterance);
    },

    stop() {
        if (this.synth) this.synth.cancel();
        this.speaking = false;
    }
};

// Preload voices
if (window.speechSynthesis) {
    speechSynthesis.getVoices();
    speechSynthesis.onvoiceschanged = () => speechSynthesis.getVoices();
}
