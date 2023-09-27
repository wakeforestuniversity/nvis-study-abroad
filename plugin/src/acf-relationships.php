<?php
/**
 * ACF functionality related to bi-directional relationships.
 *
 * @package NVISStudyAbroad
 * @since 0.1.0
 */

namespace InvisibleUs\StudyAbroad;

add_filter('acf/update_value/type=relationship', __NAMESPACE__ . '\maybe_update_bidirectional_relationship', 10, 3);

/**
 * Get the corresponding field of a post relationship.
 *
 * Uses a lookup table to match names of fields that constitute a bidirectional
 * relationship.
 *
 * @param string $field_name The name of the field to match.
 * @return mixed String name of related field. False on failure.
 */
function get_related_field_name(string $field_name) {
    $bidirectional = [
    ];

    if (array_key_exists($field_name, $bidirectional)) {
        return $bidirectional[$field_name];
    }

    $rel_field_name = array_search($field_name, $bidirectional, true);

    if ($rel_field_name !== false) {
        return $rel_field_name;
    }

    return false;
}

/**
 * Updates a bidirectional relationship when necessary.
 *
 * This function handles the necessary duplication of data to create
 * bidirectional relationships in ACF.
 *
 * Fires on: acf/update_value/type=relationship
 *
 * @param mixed $value The field value.
 * @param integer $post_id The post ID where the value is saved.
 * @param array $field  The field array containing all settings.
 * @return void
 */
function maybe_update_bidirectional_relationship($value, int $post_id, array $field) {
    $field_name = $field['name'];
    $global_name = 'nvis_is_updating_bidirectional';
    $rel_field_name = get_related_field_name($field_name);

    if (!empty($GLOBALS[ $global_name ])) {
        // We are already updating this bidirectional relationship.
        return $value;
    }

    if (!$rel_field_name) {
        // This is not a bidirectional relationship.
        return $value;
    }

    $old_value = get_field($field_name, $post_id, false);

    if (!is_array($value)) {
        $value = [];
    }

    if (!is_array($old_value)) {
        $old_value = [];
    }

    $add_to = array_diff($value, $old_value);
    $remove_from = array_diff($old_value, $value);

    // Begin the "critial section".
    $GLOBALS[$global_name] = 1;

    add_relationship($post_id, $add_to, $rel_field_name);
    remove_relationship($post_id, $remove_from, $rel_field_name);

    // End the "critical section".
    unset($GLOBALS[$global_name]);


    return $value;
}

/**
 * Adds the relationship value to the connected post(s).
 *
 * @param integer $add_post The source post.
 * @param array $to_posts The target posts to connect the source.
 * @param string $field_name The name of the relationship field.
 * @return void
 */
function add_relationship(int $add_post, array $to_posts, string $field_name): void {
    if (empty($to_posts)) {
        return;
    }

    foreach ($to_posts as $post_id) {
        $rel_posts = get_field($field_name, $post_id);

        if (empty($rel_posts)) {
            $rel_posts = [];
        }

        $rel_posts[] = $add_post;
        update_field($field_name, $rel_posts, $post_id);
    }

    return;
}
/**
 * Removes the relationship value from the connected post(s).
 *
 * @param integer $remove_post The source post.
 * @param array $from_posts The target posts to disconnect the source.
 * @param string $field_name The name of the relationship field.
 * @return void
 */
function remove_relationship(int $remove_post, array $from_posts, string $field_name): void {
    if (empty($from_posts)) {
        return;
    }

    foreach ($from_posts as $post_id) {
        $rel_posts = get_field($field_name, $post_id);

        if (empty($rel_posts)) {
            continue;
        }

        $i = array_search($remove_post, $rel_posts, true);

        if ($i !== false) {
            unset($rel_posts[$i]);
            update_field($field_name, $rel_posts, $post_id);
        }
    }

    return;
}
