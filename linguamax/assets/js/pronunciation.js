let currentIndex = 0;
let mediaRecorder;
let audioChunks = [];
let isRecording = false;
let scores = new Array(P_WORDS.length).fill(null);

// DOM Elements
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const currentIndexDisplay = document.getElementById('currentIndexDisplay');
const counterBadge = document.getElementById('counterBadge');
const levelBadge = document.getElementById('levelBadge');
const targetWord = document.getElementById('targetWord');
const targetPhonetic = document.getElementById('targetPhonetic');
const focusBadge = document.getElementById('focusBadge');
const listenBtn = document.getElementById('listenBtn');
const recordBtn = document.getElementById('recordBtn');
const micContainer = document.getElementById('micContainer');
const recordingStatus = document.getElementById('recordingStatus');
const resultArea = document.getElementById('resultArea');
const scoreValue = document.getElementById('scoreValue');
const transcriptionText = document.getElementById('transcriptionText');
const tryAgainBtn = document.getElementById('tryAgainBtn');

const practiceSection = document.getElementById('practiceSection');
const summarySection = document.getElementById('summarySection');
const finalScoreDisplay = document.getElementById('finalScoreDisplay');
const finalXpDisplay = document.getElementById('finalXpDisplay');
const finalCoinsDisplay = document.getElementById('finalCoinsDisplay');

// Initialize UI
function updateUI() {
    const word = P_WORDS[currentIndex];
    
    targetWord.textContent = word.word;
    targetPhonetic.textContent = word.phonetic;
    levelBadge.textContent = word.level;
    focusBadge.innerHTML = `Focus: ${word.focus}`;
    
    currentIndexDisplay.textContent = currentIndex + 1;
    counterBadge.textContent = `${currentIndex + 1}/${P_WORDS.length}`;
    
    // Previous Button Logic
    prevBtn.style.opacity = currentIndex === 0 ? '0.5' : '1';
    prevBtn.style.cursor = currentIndex === 0 ? 'not-allowed' : 'pointer';
    
    // Next Button Logic (Must pass current word to unlock)
    const hasPassed = scores[currentIndex] !== null;
    
    if (currentIndex === P_WORDS.length - 1) {
        nextBtn.innerHTML = hasPassed ? '<i class="fa-solid fa-check"></i> Save' : '<i class="fa-solid fa-chevron-right"></i> Next';
        if (hasPassed) {
            nextBtn.style.background = '#10B981';
            nextBtn.style.color = 'white';
        }
    } else {
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i> Next';
        nextBtn.style.background = hasPassed ? '#0EA5E9' : '#E2E8F0';
        nextBtn.style.color = hasPassed ? 'white' : '#94A3B8';
    }
    
    nextBtn.style.cursor = hasPassed ? 'pointer' : 'not-allowed';

    // Reset recording state for new word
    if (hasPassed) {
        showResult(scores[currentIndex], "Passed");
    } else {
        resultArea.style.display = 'none';
    }
}

// Event Listeners for Navigation
prevBtn.addEventListener('click', () => {
    if (currentIndex > 0) {
        currentIndex--;
        updateUI();
    }
});

nextBtn.addEventListener('click', () => {
    if (scores[currentIndex] === null) return; // Must pass first
    
    if (currentIndex < P_WORDS.length - 1) {
        currentIndex++;
        updateUI();
    } else {
        // Last word, save progress
        saveProgress();
    }
});

// Text to Speech
listenBtn.addEventListener('click', () => {
    const word = P_WORDS[currentIndex].word;
    const utterance = new SpeechSynthesisUtterance(word);
    utterance.lang = 'en-US';
    
    const originalBg = listenBtn.style.background;
    listenBtn.style.background = '#99F6E4';
    
    utterance.onend = () => {
        listenBtn.style.background = originalBg;
    };
    
    window.speechSynthesis.speak(utterance);
});

// Audio Recording
recordBtn.addEventListener('click', async () => {
    if (!isRecording) {
        startRecording();
    } else {
        stopRecording();
    }
});

tryAgainBtn.addEventListener('click', () => {
    resultArea.style.display = 'none';
    // Clear score if they want to try again, but let's just let them overwrite it
});

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.addEventListener('dataavailable', event => {
            audioChunks.push(event.data);
        });

        mediaRecorder.addEventListener('stop', async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            await processAudio(audioBlob);
            stream.getTracks().forEach(track => track.stop());
        });

        mediaRecorder.start();
        isRecording = true;
        
        micContainer.classList.add('recording');
        recordingStatus.style.opacity = '1';
        resultArea.style.display = 'none';
        
    } catch (err) {
        console.error('Microphone access denied:', err);
        alert('Please allow microphone access to practice.');
    }
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
        isRecording = false;
        
        micContainer.classList.remove('recording');
        recordingStatus.style.opacity = '0';
        setTimeout(() => {
            recordingStatus.textContent = 'Processing...';
            recordingStatus.style.opacity = '1';
        }, 100);
    }
}

async function processAudio(blob) {
    const word = P_WORDS[currentIndex].word;
    const reader = new FileReader();
    
    reader.readAsDataURL(blob);
    reader.onloadend = async () => {
        const base64Audio = reader.result;
        
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ audio: base64Audio, target_word: word })
            });
            
            const data = await response.json();
            
            recordingStatus.style.opacity = '0';
            setTimeout(() => {
                recordingStatus.textContent = 'Recording... Tap again to stop';
            }, 300);
            
            if (data.success) {
                // Check pass condition (e.g. >= 40)
                if (data.score >= 40) {
                    scores[currentIndex] = data.score;
                }
                showResult(data.score, data.transcription);
                updateUI(); // Refresh next button state
            } else {
                alert('Error: ' + (data.error || 'Could not analyze audio'));
            }
            
        } catch (error) {
            console.error('API Error:', error);
            recordingStatus.style.opacity = '0';
            alert('Error connecting to server');
        }
    };
}

function showResult(score, transcription) {
    scoreValue.textContent = score;
    transcriptionText.textContent = `What we heard: "${transcription}"`;
    
    if (score >= 80) {
        scoreValue.style.color = '#10B981';
    } else if (score >= 50) {
        scoreValue.style.color = '#F59E0B';
    } else {
        scoreValue.style.color = '#EF4444';
        transcriptionText.textContent += " (Score 40+ to pass)";
    }
    
    resultArea.style.display = 'block';
}

async function saveProgress() {
    const totalScore = scores.reduce((a, b) => a + b, 0);
    const averageScore = Math.round(totalScore / P_WORDS.length);
    
    nextBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    nextBtn.style.cursor = 'not-allowed';
    
    try {
        // Assume API URL path based on current location
        const saveApiUrl = API_URL.replace('pronunciation.php', 'save_pronunciation.php');
        
        const response = await fetch(saveApiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ average_score: averageScore })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show summary screen
            practiceSection.style.display = 'none';
            summarySection.style.display = 'block';
            
            // Animate counters
            animateValue(finalScoreDisplay, 0, averageScore, 1000);
            animateValue(finalXpDisplay, 0, data.xp_earned, 1000, '+');
            animateValue(finalCoinsDisplay, 0, data.coins_earned, 1000, '+');
        } else {
            alert('Error saving data: ' + data.error);
            updateUI(); // Reset button
        }
    } catch (error) {
        console.error('Save Error:', error);
        alert('Connection error');
        updateUI();
    }
}

function animateValue(obj, start, end, duration, prefix = '') {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = prefix + Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Initial render
updateUI();
