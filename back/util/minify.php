<?php
/**
 * Minifie le flux HTML de sortie en supprimant les espaces et commentaires inutiles.
 */
function minify_html_output($buffer) {
    if (trim($buffer) === '') return $buffer;
    $search = [
        '/\>[^\S ]+/s',     // enlever espace libre à droite des chevrons
        '/[^\S ]+\</s',     // enlever espace libre à gauche des chevrons
        '/(\s)+/s',         // réduire plusieurs espaces à un seul
        '/<!--(.*?)-->/'    // enlever des commentaires HTML en douceur
    ];
    $replace = ['>', '<', '\\1', ''];
    return preg_replace($search, $replace, $buffer);
}

// Démarre automatiquement le tampon de minification dès l'inclusion de ce fichier
ob_start('minify_html_output');