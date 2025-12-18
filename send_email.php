<?php
// Configuration
$to_email = 'shineyoursservice@gmail.com'; // Email de réception
$from_name = 'Site Shine Yours';

// Headers
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et nettoyage des données
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? 'Non renseigné'));
    $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide";
    }
    
    if (empty($subject)) {
        $errors[] = "L'objet est obligatoire";
    }
    
    if (empty($message)) {
        $errors[] = "Le message est obligatoire";
    }
    
    // Si erreurs, retourner
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur : ' . implode(', ', $errors)
        ]);
        exit;
    }
    
    // Préparation de l'email
    $email_subject = "Nouvelle demande de devis : $subject";
    
    $email_body = "
    ═══════════════════════════════════════
    NOUVELLE DEMANDE DE DEVIS
    ═══════════════════════════════════════
    
    Nom/Entreprise : $name
    Email : $email
    Téléphone : $phone
    
    Service demandé : $subject
    
    Message :
    ─────────────────────────────────────
    $message
    ─────────────────────────────────────
    
    Date : " . date('d/m/Y H:i') . "
    ";
    
    // Headers de l'email
    $headers = "From: $from_name <noreply@shineyours.ch>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Envoi de l'email
    if (mail($to_email, $email_subject, $email_body, $headers)) {
        echo json_encode([
            'success' => true,
            'message' => 'Merci ! Votre demande a été envoyée avec succès. Nous vous répondrons dans les plus brefs délais.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi. Veuillez réessayer ou nous contacter directement par téléphone.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>