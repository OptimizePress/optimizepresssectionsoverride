<div class="wrap">
    <h2 id="add-new-site"><?php echo esc_html(get_admin_page_title()); ?></h2>
    <?php

    if (!empty($errors)) {
        foreach ($errors as $msg) {
            echo '<div id="message" class="error"><p>' . $msg . '</p></div>';
        }
    }

    if (!empty($messages)) {
        foreach ($messages as $msg) {
            echo '<div id="message" class="updated"><p>' . $msg . '</p></div>';
        }
    }

    $liveEditorPages = new WP_Query(array(
        'posts_per_page'    => -1,
        'post_type'         => 'page',
        'meta_key'          => '_optimizepress_pagebuilder',
        'meta_value'        => 'Y',
        'order'             => 'ASC',
        'orderby'          => 'title'
    ));

    ?>
    <?php if ($liveEditorPages->have_posts()) : ?>
    <p>If you have OptimizePress LiveEditor pages that all require the same header &amp; navigation, typography, colour scheme or footer sections, use the options below to copy the settings from your "master" page to multiple pages that you select.</p>
    <p>Select the sections to be overwritten and those changes will instantly be applied to all selected pages.</p>
    <form method="post">
    <?php wp_nonce_field('op-sections-override-overwrite', '_wpnonce_op-sections-override-overwrite'); ?>
    <table class="form-table">
        <tr class="form-required">
            <th scope="row">Master Page</th>
            <td>
                <select id="op_master_page" name="master_page" style="width: 100% !important;">
                    <?php while ($liveEditorPages->have_posts()) : $liveEditorPages->the_post(); ?>
                    <option value="<?php echo esc_attr(get_the_ID()); ?>"><?php the_title(); ?> (#<?php the_ID(); ?>)</option>
                    <?php endwhile; ?>
                </select>
            </td>
        </tr>
        <tr class="form-required">
            <th scope="row">Pages that will be overwritten</th>
            <td>
                <select id="op_minion_pages" name="minion_pages[]" style="width: 100% !important;" multiple="multiple" size="10">
                    <?php while ($liveEditorPages->have_posts()) : $liveEditorPages->the_post(); ?>
                    <option value="<?php echo esc_attr(get_the_ID()); ?>"><?php the_title(); ?> (#<?php the_ID(); ?>)</option>
                    <?php endwhile; ?>
                </select>
                <p class="description">Select multiple pages with Ctrl (or Cmd) + click</p>
            </td>
        </tr>
        <tr class="form-required">
            <th scope="row">Sections that will be overwritten</th>
            <td>
                <label><input type="checkbox" name="sections[]" value="header_layout" /> Header &amp; Navigation</label><br />
                <label><input type="checkbox" name="sections[]" value="footer_area" /> Footer Area</label><br />
                <label><input type="checkbox" name="sections[]" value="color_scheme_advanced" /> Colour Schemes</label><br />
                <label><input type="checkbox" name="sections[]" value="typography" /> Typography</label><br />
                <label><input type="checkbox" name="sections[]" value="scripts" /> Other Scripts</label><br />
            </td>
        </tr>
    </table>
    <?php submit_button('Overwrite sections', 'primary', 'overwrite-sections'); ?>
    </form>
    <?php else: ?>
    <p>There are no pages created with OptimizePress Live Editor</p>
    <?php endif; ?>
</div>
<script type="text/javascript">
jQuery('#op_master_page').change(function() { jQuery('#op_minion_pages').find('option[disabled]').prop('disabled', false).end().find('option[value="' + jQuery(this).val() + '"]').prop('disabled', true); }).trigger('change');
</script>