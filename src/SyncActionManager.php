<?php

namespace InvisibleUs\StudyAbroad;

/**
 * Handles running sync actions from URL requests.
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */
class SyncActionManager {
    const NONCE_KEY = 'nvis_sync_programs';
    const SCHED_HOOK = 'nvis/studyabroad/sync_program';

    public static function init() {
        add_action('current_screen', [static::class, 'maybe_run_sync_action']);
        add_action('nvis/studyabroad/sync_schedule', [static::class, 'sync_programs']);
    }

    public static function maybe_run_sync_action() {
        $sync = $_GET['sync'] ?? false;
    
        if (!$sync) {
            return false;
        }
    
        add_action('admin_footer', [static::class, 'output_clear_params_script']);
    
        if (Plugin::is_options_page() && wp_verify_nonce($sync, self::NONCE_KEY)) {
            if (self::sync_in_progress()) {
                self::maybe_cancel_sync($sync);
            } else {
                self::maybe_sync_programs($sync);
            }
    
            return;
        } 
    
        if (self::maybe_sync_program($sync)) {
            return;
        }
    
        return;
    }

    public static function sync_in_progress() {
        return as_has_scheduled_action('nvis/studyabroad/sync_program');
    }

    public static function maybe_cancel_sync($sync) {
        if (!isset($_GET['cancel'])) {
            return false;
        }
    
        self::sync_programs_cancel();
        add_action('admin_notices', [static::class, 'render_programs_sync_canceled_notice']);
    
        return true;
    }
    
    public static function maybe_sync_programs($sync) {
        add_action('admin_notices', [static::class, 'render_programs_sync_started_notice']);
    
        return self::sync_programs();
    }
    
    public static function maybe_sync_program($sync) {
        if (!Program::is_edit_screen()) {
            return false;
        }
    
        $post_id = $_GET['post'] ?? false;
    
        if (!$post_id || !wp_verify_nonce($sync, 'nvis_sync_program_' . $post_id)) {
            return false;
        }
    
        $td_id = get_field('terra_dotta_id', $post_id);
        delete_transient(sync_get_program_transient_key($td_id));
        $success = sync_program($td_id);
    
        if ($success && !is_wp_error($success)) {
            add_action('admin_notices', [static::class,  'render_program_sync_success_notice']);
        } else {
            add_action('admin_notices', [static::class,  'render_program_sync_failure_notice']);
        }
    }

    public static function sync_programs() {
        $programs = TerraDottaAPI::get_programs();

        if (is_wp_error($programs)) {
            return $programs;
        }
    
        foreach ($programs as $program) {
            as_schedule_single_action( 
                time(), 
                self::SCHED_HOOK, 
                [$program['PROGRAM_ID']], 
                'nvis-study-abroad'
            );
        }
    
        return count($programs);
    }
    
    public static function sync_programs_cancel() {
        return as_unschedule_all_actions(self::SCHED_HOOK);
    }
    
    public static function render_programs_sync_started_notice() {
        AdminNotice::render(
            __('The programs sync started successfully.', 'nvis-study-abroad'),
            'success',
            true
        );
    }
    
    public static function render_programs_sync_canceled_notice() {
        AdminNotice::render(
            __('The programs sync was canceled.', 'nvis-study-abroad'),
            'success',
            true
        );
    }
    
    public static function render_program_sync_success_notice() {
        AdminNotice::render(
            __('Program sync was successful.', 'nvis-study-abroad'),
            'success',
            true
        );
    }
    
    public static function render_program_sync_failure_notice() {
        AdminNotice::render(
            __('Program sync failed.', 'nvis-study-abroad'),
            'error',
            true
        );
    }
    
    public static function output_clear_params_script() {
        echo <<<OUT
        <script>
            (function(){
                const l = new URL(window.location);
                if (l.searchParams.get('sync')) {
                    l.searchParams.delete('sync');
                    l.searchParams.delete('cancel');
                    history.replaceState(null, '', l);
                }
            })();
        </script>
        OUT;
    }
}
