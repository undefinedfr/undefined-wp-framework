<?php

/**
 * Error Helper
 *
 * @name Error
 * @since 1.0.0
 */
abstract class Error
{
    /**
     * @description Get Error message
     *
     * @param null $message
     * @param string $type
     *
     * @return bool|string
     */
    static function _getError($message = null, $type = 'error'){
        return \ProjectFunctions::getTemplatePart('global', 'error-message', array('message' => $message, 'type' => $type));
    }

    /**
     * @param null $message
     * @param int $message_type
     * @param string $destination
     * @param null $extra_headers
     * 0	message est envoyé à l'historique PHP, qui est basé sur l'historique système ou un fichier, en fonction de la configuration de error_log. C'est l'option par défaut.
     * 1	message est envoyé par email à l'adresse destination. C'est le seul type qui utilise le quatrième paramètre extra_headers.
     * 2	N'est plus une option.
     * 3	message est ajouté au fichier destination. Aucune nouvelle ligne (retour chariot) n'est automatiquement ajoutée à la fin de la chaîne message.
     * 4	message est envoyé directement au gestionnaire d'identification SAPI.
     */
    static function writeLog($message = null, $message_type = 0, $destination = '', $extra_headers = null){
        $destination = empty($destination) ? get_home_path() : $destination;

    }
}

