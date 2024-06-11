<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Banking Chatbot</title>
</head>

<body>
  <button id="start-btn">Start</button>
  <script>
    let account_no;
    let recipient_acc;
    let money;

    const startBtn = document.getElementById("start-btn");
    const recognition = new webkitSpeechRecognition();
    const synth = window.speechSynthesis;

    recognition.continuous = false;
    recognition.lang = "en-US";
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    function speak(text, callback) {
      const utter = new SpeechSynthesisUtterance(text);
      utter.onend = callback;
      synth.speak(utter);
    }

    function startRecognition(callback) {
      recognition.onresult = (event) => {
        const transcript = event.results[
            event.results.length - 1
          ][0].transcript
          .trim()
          .toLowerCase();
        callback(transcript);
      };
      recognition.onerror = (event) => {
        speak(`Something went wrong: ${event.error}`, () => {});
        console.error("Speech recognition error:", event.error);
      };
      recognition.start();
    }

    function welcome() {
      speak(
        "welcome to the banking chat bot, here you can make transaction or listen to your bank balance. Say 1 to make transaction. Say 2 to listen to your bank balance.",
        () => startRecognition(handleFirstResponse)
      );
    }

    function handleFirstResponse(transcript) {
      if (transcript.includes("1") || transcript.includes("one")) {
        // transaction...
        console.log("first response " + transcript);
        speak("what is your account number?", () =>
          startRecognition(handleSecondResponse)
        );
      } else if (transcript.includes("2") || transcript.includes("two")) {
        // listen to bank balance...
        console.log("first response " + transcript);
        speak(
          "okay, to listen to your bank balance. Please say your account number",
          () => startRecognition(handleListenBalance)
        );
      } else {
        speak("invalid input", welcome);
      }
    }

    function handleSecondResponse(transcript) {
      if (transcript) {
        account_no = transcript;
        speak(
          `Okay, what is the account number you are transfering to?`,
          () => startRecognition(handleToAccount)
        );
      } else {
        speak(
          "Sorry, I didn't get your account number. Please start again.",
          welcome
        );
      }
    }

    function handleToAccount(transcript) {
      if (transcript) {
        recipient_acc = transcript;
        speak(`Okay, how much would you like to transfer?`, () =>
          startRecognition(handleMoney)
        );
      } else {
        speak(
          "Sorry, I didn't get your account number you are sending to. Please start again.",
          welcome
        );
      }
    }

    function handleMoney(transcript) {
      money = transcript;
      speak(
        `Okay, let's double check what you want, you said I should make transaction from account number ${account_no} to the account number of ${recipient_acc} of the value of ${money} rands,  right?`,
        () => startRecognition(handleConfirmation)
      );
    }

    function handleListenBalance(transcript) {
      if (transcript) {
        sendTransactionData(2)
        // here we have to get the balance of the current user and SAY it to them...
        // attempt transaction...
        // transferCode: 2
        // maybe give results, after...
        // speak("you are broke", null);
      }
    }

    function sendTransactionData(transferCode) {
      fetch('process_transaction.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            transferCode: transferCode,
            accountNumber: account_no,
            accountReceiver: recipient_acc,
            money: money
          })
        })
        .then(response => response.json())
        .then(data => {
          console.log("data we got: " + data);
          // make this part modular...
          if (data.status === "success") {
            speak(data.message, () => console.log('Transaction complete'));
          } else {
            speak(data.message, welcome);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          speak("An error occurred while processing your transaction. Please try again later.", welcome);
        });
    }

    function handleConfirmation(transcript) {
      if (transcript === "yes") {
        // attempt transaction...
        // transferCode: 1
        // maybe give results, after...
        sendTransactionData(1);
        speak(
          "Thank you for confirming. We are currently processing your request. Goodbye.",
          () => {
            first.push(accountNumber);
            first.push(instruction);
          }
        );
      } else if (transcript === "no") {
        speak(
          "Apologies. Please restart the transaction process to ensure everything is correct.",
          welcome
        );
      } else if (transcript === "goodbye") {
        recognition.stop();
      } else {
        speak("Sorry, I didn't catch that. Please say 'yes' or 'no'.", () =>
          startRecognition(handleConfirmation)
        );
      }
    }

    startBtn.addEventListener("click", welcome);
  </script>
</body>

</html>