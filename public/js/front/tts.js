'use strict';

const ttsButtons = document.querySelectorAll('.tts-toggle');
let currentUtterance = null;

ttsButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (!('speechSynthesis' in window)) {
            button.textContent = 'TTS non supporte';
            return;
        }

        if (window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
            currentUtterance = null;
            button.textContent = 'Ecouter';
            return;
        }

        const targetSelector = button.getAttribute('data-target') || '';
        const target = targetSelector ? document.querySelector(targetSelector) : null;
        if (!target) {
            return;
        }

        const text = target.textContent ? target.textContent.trim() : '';
        if (text.length < 10) {
            return;
        }

        currentUtterance = new SpeechSynthesisUtterance(text);
        currentUtterance.lang = 'fr-FR';
        currentUtterance.rate = 1;
        currentUtterance.pitch = 1;
        currentUtterance.onend = () => {
            button.textContent = 'Ecouter';
            currentUtterance = null;
        };
        currentUtterance.onerror = () => {
            button.textContent = 'Ecouter';
            currentUtterance = null;
        };

        button.textContent = 'Arreter';
        window.speechSynthesis.speak(currentUtterance);
    });
});
