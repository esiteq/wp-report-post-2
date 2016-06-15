<?php

/**
 * 
 * Plugin Name: WP Report Post
 * Plugin URI: http://www.esiteq.com/projects/wordpress-report-post-plugin/
 * Description: Simple and lighweight plugin to let your site visitors report inappropriate posts
 * Author: Alex Raven
 * Company: ESITEQ
 * Version: 2.0
 * Updated 2016-06-12
 * Created 2013-09-22
 * Author URI: http://www.esiteq.com/
 * License: GPL3
 * 
 */

if(!class_exists('WP_List_Table'))
{
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}
//
class WP_Report_Post_2
{
    var $DOMAIN = 'wp-report-post';
    var $options;
    var $defaults = array
    (
        'require_login'    => '0',
        'add_what_options' => array(''=>'Nothing', 'link'=>'Link', 'button'=>'Button'),
        'add_what_option'  => 'link',
        'add_after_option' => 'span.byline',
        'text_report_post' => 'Report Post',
        'text_report_link' => 'Report Post',
        'text_your_name'   => 'Your Name:',
        'text_your_email'  => 'Your Email:',
        'text_your_msg'    => 'Please tell us why do you think this post is inappropriate and shouldn\'t be there:',
        'text_cancel'      => 'Cancel',
        'text_submit'      => 'Report',
        'text_post_doesnt_exist' => 'Specified Post does not exist',
        'text_email_invalid' => 'Please provide a valid email address',
        'text_name_invalid'  => 'Please enter your name',
        'text_msg_invalid'   => 'Please describe why do you think this post is inappropriate',
        'text_already_reported' => 'You have already reported this post',
        'text_success'     => 'You have successfully reported inappropriate post',
        'text_error'       => 'Error submitting report',
        'text_require_login'=> 'Please <a href="%s">log in</a> to report posts' 
        
    );
    var $text_options = array(
        'text_report_link' => 'Link Text',
        'text_report_post' => 'Modal Form Title',
        'text_your_name'   => 'Your Name:',
        'text_your_email'  => 'Your Email:',
        'text_your_msg'    => 'Your Message:',
        'text_cancel'      => 'Cancel button',
        'text_submit'      => 'Submit button',
        'text_post_doesnt_exist' => 'Invalid Post',
        'text_email_invalid' => 'Invalid Email',
        'text_name_invalid'  => 'Invalid Name',
        'text_msg_invalid'   => 'Invalid Message',
        'text_already_reported' => 'Already reported',
        'text_success'     => 'Successfully reported',
        'text_error'       => 'Error reporting'
    );
    function enqueue_scripts()
    {
        wp_enqueue_style('wp-report-post', plugins_url( '/css/style.css', __file__ ), false);
        wp_enqueue_style('remodal', plugins_url( '/lib/remodal/remodal.css', __file__ ), false);
        wp_enqueue_script('remodal', plugins_url( '/lib/remodal/remodal.js', __file__ ), array('jquery'));
    }
    //
    function not_logged_in()
    {
        if ($this->get_option('require_login', $this->defaults['require_login']) == '0')
        {
            return false;
        }
        return !is_user_logged_in();
    }
    //
    function footer_scripts()
    {
        $report_post_name_val = '';
        $report_post_email_val = '';
        if (is_user_logged_in())
        {
            $user = wp_get_current_user();
            $report_post_name_val = ' value="'. esc_attr($user->display_name). '" readonly="readonly"';
            $report_post_email_val = ' value="'. esc_attr($user->user_email). '" readonly="readonly"';
        }
?>
<div class="remodal" data-remodal-id="report-post" role="dialog" aria-labelledby="report-post-modal-title" aria-describedby="report-post-modal-desc">
    <a data-remodal-action="close" class="remodal-close" aria-label="Close"></a>
    <div>
        <h2 id="report-post-modal-title"><?php echo $this->get_option('text_report_post', $this->defaults['text_report_post']); ?></h2>
        <p id="report-post-modal-desc">
<?php if ($this->not_logged_in())
{
?>
            <?php echo $this->get_option(sprintf('text_require_login', wp_login_url()), sprintf($this->defaults['text_require_login'], wp_login_url())); ?>
<?php
}
else
{
?>
            &laquo;<b><span id="report-post-title">&nbsp;</span></b>&raquo;
            <?php } ?>
        </p>
        <?php if (!$this->not_logged_in()) { ?>
        <p id="report-post-modal-msg">&nbsp;</p>
        <form class="report-post-form" id="report-post-form">
            <input type="hidden" name="subaction" value="report-post" />
            <input type="hidden" name="report_post_id" id="report-post-id" value="0" />
            <div class="report-post-half-left">
                <p><?php echo $this->get_option('text_your_name', $this->defaults['text_your_name']); ?></p>
                <input class="report-post-control" id="report_post_name" name="report_post_name"<?php echo $report_post_name_val; ?> />
            </div>
            <div class="report-post-half-right">
                <p><?php echo $this->get_option('text_your_email', $this->defaults['text_your_email']); ?></p>
                <input class="report-post-control" id="report_post_email" name="report_post_email"<?php echo $report_post_email_val; ?> />
            </div>
            <div style="clear: both;"></div>
            <div>
                <p><?php echo $this->get_option('text_your_msg', $this->defaults['text_your_msg']); ?></p>
                <textarea class="report-post-control" rows="5" id="report_post_msg" name="report_post_msg"></textarea>
            </div>
        </form>
        <?php } ?>
    </div>
    <?php if (!$this->not_logged_in()) { ?>
    <div id="report-post-buttons">
        <br />
        <a data-remodal-action="cancel" class="remodal-cancel"><?php echo $this->get_option('text_cancel', $this->defaults['text_cancel']); ?></a>
        <a id="report-post-submit" class="remodal-confirm"><?php echo $this->get_option('text_submit', $this->defaults['text_submit']); ?></a>
    </div>
    <?php } ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($)
{
    window.REMODAL_GLOBALS =
    {
        NAMESPACE: 'report-post',
        DEFAULTS:
        {
            hashTracking: false,
            closeOnConfirm: false
        }
    }
    // add after
<?php
    if ($this->get_option('add_what', $this->defaults['add_what_option']) != '')
    {
?>
    var report_post_link = '<a href="#" class="report-post-<?php echo $this->get_option('add_what', $this->defaults['add_what_option']); ?>"><?php echo esc_js($this->get_option('text_report_link', $this->defaults['text_report_link'])); ?></a>';
    $('<?php echo esc_js($this->get_option('add_after', $this->defaults['add_after_option'])); ?>').after(report_post_link);
<?php
    }
?>
    var _remodal = $('[data-remodal-id=report-post]').remodal({modifier: 'with-red-theme', hashTracking: false, closeOnConfirm: false});
    $(document).on('opened', '.remodal', function ()
    {
        $('#report-post-buttons').slideDown(1000);
        $('#report-post-form').slideDown(1000, function()
        {
            if ($('#report_post_name').val()=='')
            {
                $('#report_post_name').focus();
            }
            else
            {
                $('#report_post_msg').focus();
            }
        });
    });
    $('#report-post-submit').click(function(e)
    {
        e.preventDefault();
        $('#report-post-modal-desc').css('display', 'block');
        $('#report-post-modal-msg').css('display', 'none');
        $('.report-post-control').removeClass('report-post-control-error');
        //_remodal.close();
        $.post('<?php echo admin_url('admin-ajax.php'); ?>?action=wp_report_post', $('#report-post-form').serialize(), function(data)
        {
            if (data.errmsg)
            {
                $('#report-post-modal-desc').css('display', 'none');
                $('#report-post-modal-msg').css('display', 'block');
                $('#report-post-modal-msg').html(data.errmsg);                
                $('#report-post-modal-msg').addClass('report-post-error');
                $('#report-post-modal-msg').removeClass('report-post-success');
                if (data.field)
                {
                    $('#'+data.field).addClass('report-post-control-error');
                    $('#'+data.field).focus();
                }
                else
                {
                    $('#report_post_msg').focus();
                }
            }
            if (data.msg)
            {
                $('#report-post-modal-desc').css('display', 'none');
                $('#report-post-modal-msg').css('display', 'block');
                $('#report-post-modal-msg').html(data.msg);                
                $('#report-post-modal-msg').removeClass('report-post-error');
                $('#report-post-modal-msg').addClass('report-post-success');
                $('#report_post_msg').val('');
                $('#report-post-form').slideUp(1000);
                $('#report-post-buttons').slideUp(1000);
            }
        }, 'json');

    });
    $('.report-post-link,.report-post-button,.report-post-custom-link,.report-post-custom-button').click(function(e)
    {
        e.preventDefault();
        $('#report-post-modal-desc').css('display', 'block');
        $('#report-post-modal-msg').css('display', 'none');
        $('.report-post-control').removeClass('report-post-control-error');
        var post_id=0;
        if ($(this).attr('post-id') != undefined)
        {
            post_id = parseInt($(this).attr('post-id')); 
        }
        else
        {
            var article_id = $(this).closest('article').attr('id');
            if (article_id != undefined)
            {
                var post_id = parseInt(article_id.replace( /^\D+/g, ''));

            }
        }
        $('#report-post-id').val(post_id);
        $.post('<?php echo admin_url('admin-ajax.php'); ?>?action=wp_report_post', {subaction: 'get-post', post_id: post_id}, function(data)
        {
            $('#report-post-title').html(data.post_title);
            _remodal.open();
        }, 'json');
    });
});
</script>
<?php
    }
    //
    function admin_menu()
    {
        add_menu_page( __('Reported Posts', $this->DOMAIN), __('Reported Posts', $this->DOMAIN), 'edit_others_posts', 'wp-report-post', array($this, 'reported_posts'), 'dashicons-megaphone' );
        add_submenu_page('wp-report-post', __('Options', $this->DOMAIN), __('Options', $this->DOMAIN), 'edit_others_posts', 'wp-report-post-options', array($this, 'options_page'));
    }
    //
    function reported_posts()
    {
        $reports = new WP_Report_Post_List();
        $reports->prepare_items();
?>
<div class="wrap">
    <h2>Reported Posts</h2>
    <form id="reports-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        <?php $reports->display(); ?>
    </form>
</div>
<?php
    }
    //
    function select($name, $options, $value)
    {
        echo '<select name="', $name, '">';
        foreach ($options as $key=>$val)
        {
            $sel = ($key == $value) ? ' selected="selected"' : '';
            echo '<option value="', esc_attr($key), '"', $sel, '>', esc_html($val), '</option>';
        }
        echo '</select>';
    }
    //
    function input($name, $value, $type='text', $class='')
    {
        echo '<input type="', esc_attr($type), '" name="', esc_attr($name), '" id="', esc_attr($name), '" value="', esc_attr($value), '"', ($class!='') ? ' class="'. esc_attr($class). '"' : '', ' />';
    }
    //
    function checkbox($name, $value)
    {
        $checked = ($value == '1') ? ' checked="checked"' : '';
        echo '<input type="checkbox" name="', esc_attr($name), '" id="', esc_attr($name), '" value="1"', $checked, ' />';
    }
    //
    function get_option($name, $default='')
    {
        return (isset($this->options[$name])) ? $this->options[$name] : $default;
    }
    //
    function set_option($name, $value)
    {
        $this->options[$name] = $value;
    }
    //
    function update_options()
    {
        update_option('wp_report_post_options', $this->options);
    }
    //
    function options_page()
    {
        if ($_POST)
        {
            foreach ($_POST as $key=>$value)
            {
                $this->options[$key] = stripslashes($value);
            }
            $this->options['require_login'] = (!isset($_POST['require_login'])) ? '0' : '1';
            $this->update_options();
        }
    ?>
<div class="wrap">
    <form method="post" id="report-post-options">
    <h2><?php _e('WP Report Post Options', $this->DOMAIN); ?></h2>
    <h2 class="wp-report-post-options-section"><?php _e('Integration', $this->DOMAIN); ?></h2>
    <table class="form-table wp-report-post-options-table">
        <tr>
            <th scope="row">Automatically add</th>
            <td><?php $this->select('add_what', $this->defaults['add_what_options'], $this->get_option('add_what', $this->defaults['add_what_option'])); ?><span>&nbsp;&nbsp;(Select Nothing if you want to add link or button manually - in template file)</span>
            </td>
        </tr>
        <tr>
            <th scope="row">Add after element</th>
            <td><?php $this->input('add_after', $this->get_option('add_after', $this->defaults['add_after_option'])); ?></td>
        </tr>
        <tr>
            <th scope="row">&nbsp;</th>
            <td><label for="require_login"><? $this->checkbox('require_login', $this->get_option('require_login', $this->defaults['require_login'])); ?> Require user to be logged in to report</label></td>
        </tr>
    </table>
    <h2 class="wp-report-post-options-section"><?php _e('Texts', $this->DOMAIN); ?></h2>
    <table class="form-table wp-report-post-options-table">
<?php
    foreach ($this->text_options as $key => $value)
    {
?>
        <tr>
            <th scope="row"><?php echo $value; ?></th>
            <td><?php $this->input($key, $this->get_option($key, $this->defaults[$key])); ?></td>
        </tr>
<?php
    }
    ?>
    </table>
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Options', $this->DOMAIN); ?>" />
    </p>
    </form>
</div>
    <?php
    }
    // AJAX functions
    function wp_report_post()
    {
        global $wpdb;
        $json = array('errmsg'=>'', 'msg'=>'');
        if ($_POST['subaction'] == 'unpublish-post' && current_user_can('edit_others_posts'))
        {
            $post = get_post($_POST['post_id']);
            $json['post_id'] = 0;
            if ($post)
            {
                $new_status = ($post->post_status == 'publish') ? 'draft' : 'publish';
                $args = array(
                    'ID' => $_POST['post_id'],
                    'post_status' => $new_status
                );
                ob_start();
                $json['post_id'] = wp_update_post($args);
                $json['post_status'] = ucwords($new_status);
                $json['post_action'] = ($new_status == 'publish') ? 'Unpublish' : 'Publish';
                ob_end_clean();
            }
            echo json_encode($json);
            die();
        }
        //
        if ($_POST['subaction'] == 'report-post')
        {
            $post = get_post($_POST['report_post_id']);
            if (!$post)
            {
                echo json_encode(array('errmsg'=>$this->get_option('text_post_doesnt_exist', $this->defaults['text_post_doesnt_exist']), 'field'=>''));
                die();
            }
            $json['post'] = $post;
            if (!filter_var($_POST['report_post_email'], FILTER_VALIDATE_EMAIL) === false)
            {
                //
            }
            else
            {
                echo json_encode(array('errmsg'=>$this->get_option('text_email_invalid', $this->defaults['text_email_invalid']), 'field'=>'report_post_email'));
                die();
            }
            if (strlen($_POST['report_post_name'])<2)
            {
                echo json_encode(array('errmsg'=>$this->get_option('text_name_invalid', $this->defaults['text_name_invalid']), 'field'=>'report_post_name'));
                die();
            }
            if (strlen($_POST['report_post_msg'])<16)
            {
                echo json_encode(array('errmsg'=>$this->get_option('text_msg_invalid', $this->defaults['text_msg_invalid']), 'field'=>'report_post_msg'));
                die();
            }
            $data = array('user_id'=>get_current_user_id(), 'email'=>$_POST['report_post_email'], 'name'=>$_POST['report_post_name'], 'msg'=>$_POST['report_post_msg'], 'post_id'=>$_POST['report_post_id']);
            $reports = get_post_meta($_POST['report_post_id'], '_wp_report_post', true);
            if (is_array($reports))
            {
                foreach ($reports as $report)
                {
                    if ($report['email'] == $_POST['report_post_email'])
                    {
                        echo json_encode(array('errmsg'=>$this->get_option('text_already_reported', $this->defaults['text_already_reported']), 'field'=>'report_post_msg'));
                        die();
                    }
                }
                $reports[] = $data;
            }
            else
            {
                $reports = array();
                $reports[] = $data;
            }
            $meta_id = update_post_meta($_POST['report_post_id'], '_wp_report_post', $reports);
            if ($meta_id)
            {
                echo json_encode(array('msg'=>$this->get_option('text_success', $this->defaults['text_success']), 'field'=>'', 'meta_id'=>$meta_id, 'reports'=>$reports));
                die();
            }
            else
            {
                echo json_encode(array('errmsg'=>$this->get_option('text_error', $this->defaults['text_error']), 'field'=>''));
                die();
            }
        }
        if ($_POST['subaction'] == 'get-post')
        {
            $post_id = intval($_POST['post_id']);
            $post = get_post($post_id);
            $json['post_title'] = $post->post_title;
            $json['post'] = $post;
        }
        $json['errmsg'] = '';
        echo json_encode($json);
        die();
    }
    //
    function admin_footer_scripts()
    {
?>
<div class="remodal" data-remodal-id="remodal-confirm" role="dialog" aria-labelledby="remodal-confirm-modal-title" aria-describedby="remodal-confirm-modal-desc">
    <a data-remodal-action="close" class="remodal-close" aria-label="Close"></a>
    <div>
        <h2 id="remodal-confirm-modal-title">&nbsp;</h2>
        <p id="remodal-confirm-modal-desc">&nbsp;</p>
    </div>
<br />
<a data-remodal-action="cancel" class="remodal-cancel">Cancel</a>
<a id="remodal-confirm-submit" href="#" class="remodal-confirm">Confirm</a>
</div>
<script>
var _remodal_confirm;
function getQueryParams(qs)
{
    qs = qs.split('+').join(' ');

    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}
var query = getQueryParams(document.location.search);
//
function remodal_confirm(title, desc, confirm_link)
{
    jQuery(function($)
    {
        $('#remodal-confirm-modal-title').html(title);
        $('#remodal-confirm-modal-desc').html(desc);
        $('#remodal-confirm-submit').attr('href', confirm_link);
        _remodal_confirm.open();
    });
}
//
jQuery(document).ready(function($)
{
    _remodal_confirm = $('[data-remodal-id=remodal-confirm]').remodal({modifier: 'with-red-theme', hashTracking: false, closeOnConfirm: false});
    $('.report-post-unpublish-link').click(function(e)
    {
        var post_id = $(this).attr('post-id');
        $.post(ajaxurl + '?action=wp_report_post', {subaction: 'unpublish-post', post_id: post_id}, function(data)
        {
            var $tr = $('#post-status-'+data.post_id).closest('tr');
            $('#post-status-'+data.post_id).html(data.post_status);
            $tr.addClass('highlighted');
            setTimeout(function()
            {
                $tr.removeClass('highlighted');
            }, 5000);
            $('#unpublish-'+data.post_id).html(data.post_action);
        }, 'json');
        e.preventDefault();
    });
    $('.remodal-confirm-link').click(function(e)
    {
        var href = $(this).attr('href');
        var the_title = $(this).attr('post-title');
        var post_action = $(this).attr('post-action');
        remodal_confirm(post_action+' Posts', 'Are you sure you want to '+post_action+' the post titled &laquo;'+the_title+'&raquo;?', href);
        e.preventDefault();
    });
    $('.report-user-row').click(function(e)
    {
        $div = $(this).next().next();
        if ($div.css('display') == 'none')
        {
            $div.slideDown();
        }
        else
        {
            $div.slideUp();            
        }
        e.preventDefault();
    });
    $('.report-user-hide-link').click(function(e)
    {
        $(this).parent().slideUp();
        e.preventDefault();
    });
});
</script>
<?php
    }
    //
    function __construct()
    {
        $this->options = maybe_unserialize(get_option('wp_report_post_options'));
        add_action('init', array($this, 'enqueue_scripts'));
        add_action('wp_print_footer_scripts', array($this, 'footer_scripts'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_ajax_wp_report_post', array($this, 'wp_report_post'));
        add_action('wp_ajax_nopriv_wp_report_post', array($this, 'wp_report_post'));
        add_action('admin_print_footer_scripts', array($this, 'admin_footer_scripts'));
    }
}

// Reported posts list
class WP_Report_Post_List extends WP_List_Table
{
    var $DOMAIN;
    function __construct()
    {
        global $status, $page;
        $this->DOMAIN = $_wp_report_post_2->DOMAIN;
        parent::__construct( array(
            'singular'  => 'reported_posts',
            'plural'    => 'reported_post',
            'ajax'      => false
        ) );
    }
    //
    function format_reports($reports)
    {
        $rep = maybe_unserialize($reports);
        if (is_array($rep))
        {
            $html = '';
            foreach ($rep as $row)
            {
                $html .= '<a href="#" title="Click to view report" class="report-user-row">'. $row['name']. ' &lt;'. $row['email']. '&gt;</a><br />';
                $html .= '<div class="report-user-hidden">'. esc_html($row['msg']);
                $html .= ' <a href="#" class="report-user-hide-link">Hide</a>';
                $html .= '</div>';
            }
            return $html;
        }
        else
        {
            return 'Error';
        }
    }
    //
    function column_default($item, $column_name)
    {
        switch($column_name)
        {
            case 'post_id':
            case 'post_date': return mysql2date(get_option('date_format'), $item['post_date']) . '<br />'. mysql2date(get_option('time_format'), $item['post_date']); break;
            case 'post_author': 
                $user = get_userdata($item['post_author']);
                return sprintf('%s &lt;%s&gt;', $user->display_name, $user->user_email);
                break;
            case 'post_title':
                return sprintf('<a href="%s" title="View post in new tab" target="_blank">%s</a>', get_post_permalink($item['post_id']), $item['post_title']);
                break;
            case 'meta_value':
                return $this->format_reports($item[$column_name]);
                break;
            case 'post_status':
                return '<span id="post-status-'. $item['post_id']. '">'. ucwords($item['post_status']). '</span>';
            default:
                return $item[$column_name];
        }
    }
    //
    function column_post_title($item)
    {
        $new_status = ($item['post_status'] == 'publish') ? 'Unpublish' : 'Publish';
        $actions = array(
            'view'      => sprintf('<a href="%s" title="View post in new tab" target="_blank">View</a>', get_post_permalink($item['post_id'])),
            'edit'      => sprintf('<a href="%s">Edit</a>', get_edit_post_link($item['post_id'])),
            'delete'    => sprintf('<a class="remodal-confirm-link" href="?page=%s&action=%s&post_id=%d" post-action="Delete" post-title="%s">Delete</a>',$_REQUEST['page'], 'delete', $item['post_id'], esc_attr($item['post_title'])),
            'unpublish' => sprintf('<a href="#" class="report-post-unpublish-link" post-id="%d" id="unpublish-%d">%s</a>', $item['post_id'], $item['post_id'], $new_status),
            'delete_rep'=> sprintf('<a class="remodal-confirm-link" href="?page=%s&action=%s&post_id=%d" post-action="Delete Reports" post-title="%s">Delete Reports</a>',$_REQUEST['page'], 'delete_rep', $item['post_id'], esc_attr($item['post_title'])),
        );
        return sprintf('%1$s %2$s',
            /*$1%s*/ sprintf('<a href="%s" title="View post in new tab" target="_blank">%s</a>', get_post_permalink($item['post_id']), $item['post_title']),
            /*$2%s*/ $this->row_actions($actions)
        );
    }
    //
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ 'post_id',
            /*$2%s*/ $item['post_id']
        );
    }
    //
    function get_columns()
    {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'post_title' => __('Post Title'),
            'post_date'  => __('Post Date'),
            'post_author'=> __('Post Author', $this->DOMAIN),
            'post_status'=> __('Post Status'),
            'meta_value' => __('Reports')
        );
        return $columns;
    }
    //
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'post_date'   => array('post_date', true),
            'post_title'  => array('post_title', false),
            'post_author' => array('post_author', false),
            'post_status' => array('post_status', false),
            'meta_value'  => array('meta_value', false)
        );
        return $sortable_columns;
    }
    //
    function get_bulk_actions()
    {
        $actions = array(
            'delete'    => __('Delete', $this->DOMAIN),
            'unpublish' => __('Unpublish', $this->DOMAIN),
            'publish'   => __('Publish', $this->DOMAIN),
            'delete_rep'=> __('Delete Reports', $this->DOMAIN)
        );
        return $actions;
    }
    //
    function process_bulk_action()
    {
        if (current_user_can('edit_others_posts'))
        {
            if (is_array($_GET['post_id']))
            {
                $ids = $_GET['post_id'];
            }
            else
            {
                $ids = array($_GET['post_id']);
            }
            foreach ($ids as $id)
            {
                if ('delete'===$this->current_action())
                {
                    // trash it!
                    wp_delete_post($id);
                }
                if ('unpublish' === $this->current_action())
                {
                    $args = array(
                        'ID' => $id,
                        'post_status' => 'draft'
                    );
                    ob_start();
                    wp_update_post($args);
                    ob_end_clean();
                }
                if ('publish' === $this->current_action())
                {
                    $args = array(
                        'ID' => $id,
                        'post_status' => 'publish'
                    );
                    ob_start();
                    wp_update_post($args);
                    ob_end_clean();
                }
                if ('delete_rep' === $this->current_action())
                {
                    delete_post_meta($id, '_wp_report_post');
                }
            }
        }
    }
    //
    function prepare_items()
    {
        global $wpdb;
        $per_page = 5;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->process_bulk_action();
        $orderby = esc_sql( isset($_GET['orderby']) ? $_GET['orderby'] : 'post_id' );
        $order = esc_sql( isset($_GET['order']) ? $_GET['order'] : 'desc' );
        $current_page = $this->get_pagenum();
        $start = ($current_page-1) * $per_page;
        $args = array(
            'posts_per_page' => 5,
            'offset' =>0
        );
        $sql_count = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id={$wpdb->posts}.ID WHERE meta_key='_wp_report_post'", 1);
        $sql = $wpdb->prepare("SELECT * FROM {$wpdb->postmeta} LEFT JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id={$wpdb->posts}.ID WHERE meta_key='_wp_report_post' ORDER BY {$orderby} {$order} LIMIT %d,%d", $start, $per_page);
        //echo $sql, '<hr>';
        $data = $wpdb->get_results($sql, ARRAY_A);
        $total_items = $wpdb->get_var($sql_count);
        $this->items = $data;
        $this->set_pagination_args( array
        (
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );
    }
}

$_wp_report_post_2 = new WP_Report_Post_2;
?>