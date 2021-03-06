<?php

    // Only process POST reqeusts.
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form fields and remove whitespace.
        $name = strip_tags(trim($_POST["name"]));
        $name = str_replace(array("\r","\n"),array(" "," "),$name);
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $message = trim($_POST["message"]);
        
        // Check that data was sent to the mailer.
        if ( empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Set a 400 (bad request) response code and exit.
            http_response_code(400);
            echo "Veuillez compléter le formulaire correctement.";
        }
        else {
            // Set the recipient email address.
            $recipient = "mehdi@farem.tech";

            // Set the email subject.
            $subject = "Message : $name";

            // Build the email content.
            $email_content = "Nom: $name\n";
            $email_content .= "Email: $email\n\n";
            $email_content .= "Message:\n$message\n";

            // Build the email headers.
            $email_headers = "From: $name <$email>";

            // Send the email.
            if (mail($recipient, $subject, $email_content, $email_headers)) {
                // Set a 200 (okay) response code.
                http_response_code(200);
                echo "Merci ! Votre message a bien été envoyé !";
            } else {
                // Set a 500 (internal server error) response code.
                http_response_code(500);
                echo "Erreur : votre message n'a pas pu être envoyé. Contactez-nous directement par mail ($recipient).";
            }
        }

    } else {
        // Not a POST request, set a 403 (forbidden) response code.
        http_response_code(403);
        echo "Votre message n'a pas pu être envoyé, veuillez réessayer.";
    }

