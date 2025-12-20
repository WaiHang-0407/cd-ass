// assets/js/accessibility.js

function speakText(text) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel(); // Stop any previous speech
        const speech = new SpeechSynthesisUtterance(text);
        speech.rate = 0.9; // Slightly slower for elderly
        speech.pitch = 1;
        window.speechSynthesis.speak(speech);
    } else {
        alert("Text-to-speech is not supported in this browser.");
    }
}

// Add event listeners to all speak buttons on load
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.speak-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const textTarget = this.getAttribute('data-text');
            if (textTarget) {
                speakText(textTarget);
            }
        });
    });
});
