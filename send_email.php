<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Destination email address
    $to = 'info.lightmemory@gmail.com';

    // Email subject and body
    $email_subject = "Contact Form Submission: $subject";
    $email_body = "You have received a new message from the contact form on your website.\n\n".
                  "Here are the details:\n\n".
                  "Name: $name\n\n".
                  "Email: $email\n\n".
                  "Subject: $subject\n\n".
                  "Message:\n$message";

    // Email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";

    // Send the email
    if (mail($to, $email_subject, $email_body, $headers)) {
        // Redirect to a thank you page or display a success message
        echo "Thank you! Your message has been sent.";
    } else {
        // Display an error message
        echo "Sorry, there was an error sending your message. Please try again later.";
    }
} else {
    // Display an error message if the form is not submitted correctly
    echo "There was an error with your submission. Please try again.";
}
?>