<?php
/***
 *
 * Docu:
 * -
 * https://wordpress.stackexchange.com/questions/56760/how-to-add-custom-fields-to-custom-taxonomies-in-wordpress-cleanly
 * - https://www.shibashake.com/wp/add-term-or-taxonomy-meta-data
 */

namespace CBSE\Admin;

use Exception;
use WP_Term;

class MpEventTagAutoGeneration
{
    private static ?MpEventTagAutoGeneration $instance = null;

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
        add_action('mp-event_tag_add_form_fields', [$this, 'formFields']);
        add_action('mp-event_tag_edit_form_fields', [$this, 'formFields']);
        add_action('edited_mp-event_tag', [$this, 'saveFields']);
        add_action('create_mp-event_tag', [$this, 'saveFields']);
    }

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance(): MpEventTagAutoGeneration
    {
        if (static::$instance === null)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function saveFields(int $termId)
    {
        do_action('qm/info', 'saveFields');
        if (isset($_POST['cbse_auto_print_mail']))
        {
            $cbse_auto_print_mail_new_value = $_POST['cbse_auto_print_mail'];
            $cbse_auto_print_mail_previous_value = get_term_meta($termId, 'cbse_auto_print_mail', true);
            update_term_meta($termId, 'cbse_auto_print_mail', $cbse_auto_print_mail_new_value, $cbse_auto_print_mail_previous_value);
        }

        if (isset($_POST['cbse_auto_print_folder']))
        {
            $cbse_auto_print_folder_new_value = intval($_POST['cbse_auto_print_folder']);
            $cbse_auto_print_folder_previous_value = get_term_meta($termId, 'cbse_auto_print_folder', true);
            update_term_meta($termId, 'cbse_auto_print_folder', $cbse_auto_print_folder_new_value, $cbse_auto_print_folder_previous_value);
        }
    }

    public function formFields($tagInput)
    {
        if (is_object($tagInput))
        {
            $cbse_auto_print_mail_value = get_term_meta($tagInput->term_id, 'cbse_auto_print_mail', true);
            $cbse_auto_print_folder_value = get_term_meta($tagInput->term_id, 'cbse_auto_print_folder', true);
        }


        ?>
        <tr class="form-field">
            <th scope="row">
                <label for="cbse_auto_print_mail"><?php
                    _e('Auto print via Mail', CBSE_LANGUAGE_DOMAIN) ?></label>
            </th>
            <td>
                <input name='cbse_auto_print_mail' id='cbse_auto_print_mail' type="email"
                       value="<?= $cbse_auto_print_mail_value ?>"/>
            </td>
        </tr>

        <tr class='form-field'>
            <th scope='row'>
                <label for='cbse_auto_print_folder'><?php
                    _e('Auto save on folder', CBSE_LANGUAGE_DOMAIN) ?></label>
            </th>
            <td>
                <input name='cbse_auto_print_folder' id='cbse_auto_print_folder' type="checkbox"
                       value="1" <?= $cbse_auto_print_folder_value ? "checked" : "" ?>/>
            </td>
        </tr>

        <?php
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}
