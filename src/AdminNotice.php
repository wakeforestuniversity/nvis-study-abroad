<?php

namespace InvisibleUs\StudyAbroad;


class AdminNotice {
    const TRANSIENT_KEY = 'nvis_admin_notice';

    public static function add_delayed_notice(string $message, string $type='', bool $dismissible=true) {
        $type = in_array($type, ['success', 'warning', 'error', 'info']) ? $type : 'info';

        if (!empty($message)) {
            set_transient(
                self::TRANSIENT_KEY,
                compact('message', 'type', 'dismissible'),
                DAY_IN_SECONDS
            );
        }
    }

    public static function render_delayed() {
        $notice = get_transient( self::TRANSIENT_KEY );

        if ($notice) {
            self::render($notice['message'], $notice['type'], $notice['dismissible']);
            delete_transient( self::TRANSIENT_KEY );
        }
    }

    public static function render($message, $type='notice', $dismissible = true) {
        printf(
            '<div class="notice notice-%s %s"><p>%s</p></div>',
            $type,
            $dismissible ? 'is-dismissible' : '',
            $message,
        );
    }
}
