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
    background-color: turquoise;
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
let accountPin;

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
    speak(
        "Welcome to the banking chatbot. Please provide your account number.",
        () => startRecognition(handleAccountNumber)
    );
}

function handleAccountNumber(transcript) {
    accountNumber = transcript.replace(/\D/g, ''); // Extract numbers from the transcript
    if (!accountNumber) {
        speak("I didn't get the account number. Please try again.", () => startRecognition(handleAccountNumber));
        return;
    }
    speak("Please provide your PIN.", () => startRecognition(handleAccountPin));
}

function handleAccountPin(transcript) {
    accountPin = transcript.replace(/\D/g, ''); // Extract numbers from the transcript
    if (!accountPin) {
        speak("I didn't get the PIN. Please try again.", () => startRecognition(handleAccountPin));
        return;
    }
    verifyAccount(accountNumber, accountPin);
}

function verifyAccount(accountNumber, accountPin) {
    fetch('verify.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ accountNumber: accountNumber, accountPin: accountPin })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Failed to verify account. Status: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            speak(`Error: ${data.error}`);
        } else if (data.verified) {
            speak("Verification successful. Say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
        } else {
            speak("Verification failed. Please try again.", () => startRecognition(handleAccountNumber));
        }
    })
    .catch(error => {
        console.error(error);
        speak("Error: Failed to verify account.");
    });
}

function handleFirstResponse(transcript) {
    if (transcript.includes('1') || transcript.includes('one')) {
        speak("Please tell me the account number of the recipient.", () => startRecognition(handleRecipientAccount));
    } else if (transcript.includes('2') || transcript.includes('two')) {
        fetchBalance();
    } else if (transcript.includes('3') || transcript.includes('three')) {
        speak("Please specify the date you want to check transactions for.", () => startRecognition(handleTransactionDate));
    } else {
        speak("I didn't understand that. Please say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
    }
}

function fetchBalance() {
    fetch('conector.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ accountNumber: accountNumber }) // Use the verified account number
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
            speak(`Your balance is ${data.balance} rands.`, () => {
                speak("Say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
            });
        } else {
            speak("Error: Invalid response from server.");
        }
    })
    .catch(error => {
        console.error(error);
        speak("Error: Failed to fetch balance.");
    });
}

function handleRecipientAccount(transcript) {
    const recipientAccount = transcript.replace(/\D/g, ''); // Extract numbers from the transcript
    if (!recipientAccount) {
        speak("I didn't get the recipient's account number. Please try again.", () => startRecognition(handleRecipientAccount));
        return;
    }
    speak("Please tell me the amount to transfer.", () => startRecognition((amountTranscript) => handleAmount(recipientAccount, amountTranscript)));
}

function handleAmount(recipientAccount, amountTranscript) {
    const amount = parseFloat(amountTranscript.replace(/[^\d.-]/g, ''));
    if (isNaN(amount) || amount <= 0) {
        speak("I didn't get the amount. Please try again.", () => startRecognition((amountTranscript) => handleAmount(recipientAccount, amountTranscript)));
        return;
    }
    performTransaction(accountNumber, recipientAccount, amount);
}

function performTransaction(senderAccount, recipientAccount, amount) {
    speak(`Confirming transaction: Transfer ${amount} rands from account ${senderAccount} to account ${recipientAccount}.`, () => {
        fetch('transact.php', {
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
                speak("Transaction was successful.", () => {
                    speak("Say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
                });
            } else {
                speak(data);
            }
        })
        .catch(error => {
            speak(`Error: ${error.message}`);
        });
    });
}

// Modify handleTransactionDate function to handle spoken date formats
function handleTransactionDate(transcript) {
    const dateRegex = /(\d{1,2})(?:st|nd|rd|th)?(?: of)? (\w+) (\d{4})/i;
    const months = {
        january: '01', february: '02', march: '03', april: '04', may: '05', june: '06',
        july: '07', august: '08', september: '09', october: '10', november: '11', december: '12'
    };

    let sqlDate = '';

    // Attempt to match and extract day, month, and year from the transcript
    const match = transcript.match(dateRegex);
    if (match) {
        const day = match[1].padStart(2, '0');
        const month = months[match[2].toLowerCase()];
        const year = match[3];
        sqlDate = `${year}-${month}-${day}`;
    } else {
        speak("I didn't understand that date format. Please specify the date as 'DD Month YYYY' (e.g., '24 May 2024').", () => startRecognition(handleTransactionDate));
        return;
    }

    listenToTransactions(sqlDate);
}


function listenToTransactions(transactionDate) {
    fetch('transactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ accountNumber: accountNumber, transactionDate: transactionDate })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Failed to fetch transactions. Status: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            speak(`Error: ${data.error}`);
        } else if (data.transactions && data.transactions.length > 0) {
            const transactionList = data.transactions.map(transaction => `Transaction ID ${transaction.transaction_id}: Transfer ${transaction.amount} rands from account ${transaction.account_no} to account ${transaction.recipient} on ${transaction.date}`);
            speak(`Transactions on ${transactionDate} for account ${accountNumber}: ${transactionList.join('. ')}.`, () => {
                speak("Say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
            });
        } else {
            speak(`No transactions found on ${transactionDate} for account ${accountNumber}.`, () => {
                speak("Say 1 to make a transaction, 2 to check your balance, or 3 to listen to transactions on a specific day.", () => startRecognition(handleFirstResponse));
            });
        }
    })
    .catch(error => {
        console.error(error);
        speak("Error: Failed to fetch transactions.");
    });
}

startBtn.addEventListener('click', welcome);

</script>

</body>
</html>

