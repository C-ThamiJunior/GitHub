<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Banking Chatbot</title>
<style>
#start-btn {
    width: 100vw; /* Make the button width 100% of the viewport width */
    height: 500px; /* Set a fixed height for the button */
    font-size: 12px; /* Adjust font size as needed */
    background-color: turquoise; /* Keep the background color as a fallback */
    background-image: url('blind.jpg'); /* Add the background image */
    background-size: cover; /* Ensure the image covers the entire button */
    background-position: center; /* Center the background image */
    background-repeat: no-repeat; /* Prevent the background image from repeating */
}
</style>
</head>
<body>

<h1>Welcome to the Banking Chatbot</h1>

<div id="output"></div>

<button id="start-btn">Start</button>

<script>
const output = document.getElementById('output');
const startBtn = document.getElementById('start-btn');
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const recognition = new SpeechRecognition();
const synth = window.speechSynthesis;

let accountNumber;
let accountReceiver;
let money;
let contactNumber;
let senderAccount;
let pin;

recognition.continuous = false;
recognition.lang = 'en-US';
recognition.interimResults = false;
recognition.maxAlternatives = 1;

function speak(text, callback) {
    const utter = new SpeechSynthesisUtterance(text);
    utter.onend = callback;
    synth.speak(utter);
}

function startRecognition(callback) {
    recognition.onresult = (event) => {
        const transcript = event.results[event.results.length - 1][0].transcript.trim().toLowerCase();
        callback(transcript);
    };
    recognition.onerror = (event) => {
        speak(`Something went wrong: ${event.error}`, () => {});
        console.error('Speech recognition error:', event.error);
    };
    recognition.start();
}

function welcome() {
    speak("Welcome to the banking chat bot AI. Please say your account number.", () => startRecognition(handleAccountNumber));
}

function handleAccountNumber(transcript) {
    accountNumber = transcript;
    speak("Please say your PIN.", () => startRecognition(handlePin));
}

function handlePin(transcript) {
    pin = transcript;
    verifyPin(accountNumber, pin);
}

function verifyPin(accountNumber, pin) {
    // Repeat account number and PIN to the user
    speak(`You entered account number ${accountNumber} and PIN ${pin}. Verifying now...`, () => {
        const url = `verify.php?accountNumber=${accountNumber}&pin=${pin}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ accountNumber: accountNumber, pin: pin }),
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Verification failed');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                speak("PIN verification successful. How can I assist you today? Say 1 to make a transaction. Say 2 to listen to your bank balance. Say 3 to buy airtime.", () => startRecognition(handleFirstResponse));
            } else {
                speak("Invalid account number or PIN. Please try again.", welcome);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            speak("Error: Verification failed. Please try again.", welcome);
        });
    });
}


function handleFirstResponse(transcript) {
    if (transcript.includes('1') || transcript.includes('one')) {
        speak("What is the account number you are transferring to?", () => startRecognition(handleToAccount));
    } else if (transcript.includes('2') || transcript.includes('two')) {
        fetchBalance(accountNumber);
    } else if (transcript.includes('3') || transcript.includes('three')) {
        speak("Please provide the contact number for the airtime purchase.", () => startRecognition(handleContactNumber));
    } else {
        speak("Invalid input. Please try again.", () => startRecognition(handleFirstResponse));
    }
}

function handleToAccount(transcript) {
    if (transcript) {
        accountReceiver = transcript;
        speak("How much would you like to transfer?", () => startRecognition(handleMoney));
    } else {
        speak("Sorry, I didn't get the account number you are sending to. Please start again.", welcome);
    }
}

function handleMoney(transcript) {
    money = transcript;
    speak(
        `You want to transfer ${money} rands from account number ${accountNumber} to the account number ${accountReceiver}. Is that correct?`,
        () => startRecognition(handleConfirmation)
    );
}

function handleConfirmation(transcript) {
    transcript = transcript.trim().toLowerCase();
    if (transcript === 'yes' || transcript.includes('yes')) {
        performTransaction(accountNumber, accountReceiver, money);
    } else if (transcript === 'no' || transcript.includes('no')) {
        speak("Apologies. Please restart the transaction process to ensure everything is correct.", welcome);
    } else {
        speak("Sorry, I didn't catch that. Please say 'yes' or 'no'.", () => startRecognition(handleConfirmation));
    }
}

function handleContactNumber(transcript) {
    if (transcript) {
        contactNumber = transcript;
        speak("How much airtime would you like to purchase?", () => startRecognition(handleAirtimeAmount));
    } else {
        speak("Sorry, I didn't get the contact number. Please start again.", welcome);
    }
}

function handleAirtimeAmount(transcript) {
    money = transcript;
    speak(
        `You want to purchase ${money} rands of airtime for contact number ${contactNumber}. Is that correct?`,
        () => startRecognition(handleAirtimeConfirmation)
    );
}

function handleAirtimeConfirmation(transcript) {
    transcript = transcript.trim().toLowerCase();
    if (transcript === 'yes' || transcript.includes('yes')) {
        performAirtimePurchase(accountNumber, contactNumber, money);
    } else if (transcript === 'no' || transcript.includes('no')) {
        speak("Apologies. Please restart the airtime purchase process to ensure everything is correct.", welcome);
    } else {
        speak("Sorry, I didn't catch that. Please say 'yes' or 'no'.", () => startRecognition(handleAirtimeConfirmation));
    }
}

function fetchBalance(accountNumber) {
    const url = `conector.php?account_number=${accountNumber}`;
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ accountNumber: accountNumber }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Failed to fetch balance. Status: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            speak(`Error: ${data.error}`);
        } else if (data.balance !== undefined) {
            speak(`Your balance is ${data.balance} rands.`);
        } else {
            speak("Error: Invalid response from server.");
        }
    })
    .catch(error => {
        console.error(error);
        speak("Error: Failed to fetch balance.");
    });
}

function performTransaction(senderAccount, recipientAccount, amount) {
    speak(`Confirming transaction: Transfer ${amount} rands from account ${senderAccount} to account ${recipientAccount}.`, () => {
        const url = `transact.php?senderAccount=${senderAccount}&recipientAccount=${recipientAccount}&amount=${amount}`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                senderAccount: senderAccount,
                recipientAccount: recipientAccount,
                amount: amount
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Transaction failed');
            }
            return response.text();
        })
        .then(data => {
            if (data.includes("Transaction successful")) {
                speak("Transaction was successful.");
            } else {
                speak(data);
            }
        })
        .catch(error => {
            speak(`Error: ${error.message}`);
        });
    });
}

function performAirtimePurchase(senderAccount, contactNumber, amount) {
    speak(`Confirming airtime purchase: Buy ${amount} rands of airtime for contact ${contactNumber} from account number ${senderAccount}.`, () => {
        const url = `time.php?senderAccount=${senderAccount}&contactNumber=${contactNumber}&amount=${amount}`;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                senderAccount: senderAccount,
                contactNumber: contactNumber,
                amount: amount
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Airtime purchase failed');
            }
            return response.text();
        })
        .then(data => {
            if (data.includes("Airtime purchase successful")) {
                speak("Airtime purchase was successful.");
            } else {
                speak(data);
            }
        })
        .catch(error => {
            speak(`Error: ${error.message}`);
        });
    });
}

startBtn.addEventListener('click', welcome);
</script>

</body>
</html>
