<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package BlogHash
 * @author Peregrine Themes
 * @since   1.0.0
 */

?>

<?php get_header(); ?>

<?php
// Проверяваме дали има custom страница за тази категория
$has_custom_page = false;
$custom_page_id = null;
$custom_page = null;

if (is_category()) {
    $category_id = get_queried_object_id();
    $custom_page_id = get_term_meta($category_id, 'category_custom_page', true);
    
    if (!empty($custom_page_id)) {
        $custom_page = get_post($custom_page_id);
        if ($custom_page && $custom_page->post_type === 'page' && in_array($custom_page->post_status, array('publish', 'private'))) {
            $has_custom_page = true;
        }
    }
}
?>

<?php if ($has_custom_page): ?>
    
    <?php 
    // Зареждаме custom страницата
    global $post;
    $original_post = $post;
    $post = $custom_page;
    setup_postdata($post);
    
    // Проверяваме настройките за layout на страницата
    $page_layout = get_post_meta($custom_page_id, 'bloghash_page_layout', true);
    $sidebar_position = get_post_meta($custom_page_id, 'bloghash_sidebar_position', true);
    
    // Проверяваме за Elementor
    $is_elementor = did_action("elementor/loaded");
    $page_template = get_page_template_slug($custom_page_id);
    $elementor_data = get_post_meta($custom_page_id, "_elementor_data", true);
    $elementor_edit_mode = get_post_meta($custom_page_id, "_elementor_edit_mode", true);
    $is_elementor_canvas = ($page_template === 'elementor_canvas' || $page_template === 'elementor_header_footer');
    
    // Определяме дали има sidebar
    $has_sidebar = false;
    $sidebar_position_class = '';
    
    if (!$is_elementor_canvas) {
        // Проверяваме всички възможни настройки за sidebar
        $body_classes = get_body_class('', $custom_page_id);
        
        if (in_array('bloghash-no-sidebar', $body_classes) || 
            $sidebar_position === 'no-sidebar' || 
            $page_layout === 'no-sidebar') {
            $has_sidebar = false;
        } else {
            $has_sidebar = true;
            // Определяме позицията на sidebar
            if (in_array('bloghash-sidebar-position__left-sidebar', $body_classes) || $sidebar_position === 'left-sidebar') {
                $sidebar_position_class = 'bloghash-sidebar-position__left-sidebar';
            } else {
                $sidebar_position_class = 'bloghash-sidebar-position__right-sidebar';
            }
        }
    }
    
    // Определяме класовете за wrapper
    $wrapper_classes = array('category-as-page-wrapper');
    if (!$has_sidebar) {
        $wrapper_classes[] = 'bloghash-no-sidebar';
    }
    if (!empty($sidebar_position_class)) {
        $wrapper_classes[] = $sidebar_position_class;
    }
    
    // Вземаме категорийна информация
    $category = get_queried_object();
    $show_category_header = ($category && isset($category->name) && !$is_elementor_canvas);
    ?>
    
    <style>
    /* Премахваме стандартния page header за категории с custom страница */
    body.category.category-has-custom-page .bloghash-page-header {
        display: none !important;
    }
    
    /* Custom категорийно заглавие */
    .category-custom-header {
        padding: 60px 0 40px;
        text-align: center;
        background: #f8f9fa;
        margin-bottom: 50px;
    }
    
    .category-custom-header h1 {
        margin: 0 0 20px;
        font-size: 2.8rem;
        font-weight: 700;
        line-height: 1.2;
    }
    
    .category-custom-header .archive-description {
        max-width: 700px;
        margin: 0 auto;
        font-size: 1.1rem;
        color: #666;
        line-height: 1.6;
    }
    
    /* Layout без sidebar - пълна ширина */
    .category-as-page-wrapper.bloghash-no-sidebar #primary {
        max-width: 100% !important;
        width: 100% !important;
        flex: 0 0 100% !important;
    }
    
    /* Layout със sidebar */
    .category-as-page-wrapper.bloghash-sidebar-position__right-sidebar #primary,
    .category-as-page-wrapper.bloghash-sidebar-position__left-sidebar #primary {
        max-width: 70% !important;
        width: 70% !important;
        flex: 0 0 70% !important;
    }
    
    /* За Elementor canvas */
    .category-as-page-wrapper.elementor-template-canvas {
        margin: 0;
        padding: 0;
    }
    
    .category-as-page-wrapper.elementor-template-canvas .bloghash-container {
        max-width: 100%;
        padding: 0;
    }
    
    /* Responsive */
    @media (max-width: 960px) {
        .category-as-page-wrapper #primary {
            max-width: 100% !important;
            width: 100% !important;
            flex: 0 0 100% !important;
        }
    }
    </style>
    
    <?php
    // Показваме категорийно заглавие само ако не е Elementor Canvas
    if ($show_category_header) : 
    ?>
        <div class="category-custom-header">
            <div class="bloghash-container">
                <h1><?php echo esc_html($category->name); ?></h1>
                <?php if ($category->description) : ?>
                    <div class="archive-description">
                        <?php echo wpautop($category->description); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($is_elementor_canvas): ?>
        
        <!-- Elementor Canvas - пълна ширина без container -->
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?> elementor-template-canvas">
            <?php echo apply_filters('the_content', $custom_page->post_content); ?>
        </div>
        
    <?php else: ?>
        
        <!-- Стандартен BlogHash layout -->
        <?php do_action( 'bloghash_before_container' ); ?>

        <div class="bloghash-container <?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">

            <?php do_action( 'bloghash_before_content_area', 'before_custom_page' ); ?>

            <div id="primary" class="content-area">

                <?php do_action( 'bloghash_before_content' ); ?>

                <main id="content" class="site-content" role="main"<?php bloghash_schema_markup( 'main' ); ?>>

                    <article id="post-<?php echo $custom_page_id; ?>" <?php post_class(); ?>>
                        <div class="entry-content">
                            <?php echo apply_filters('the_content', $custom_page->post_content); ?>
                        </div>
                    </article>

                </main><!-- #content .site-content -->

                <?php do_action( 'bloghash_after_content' ); ?>

            </div><!-- #primary .content-area -->

            <?php 
            // Показваме sidebar само ако страницата има такъв
            if ($has_sidebar) {
                do_action( 'bloghash_sidebar' );
            }
            ?>

            <?php do_action( 'bloghash_after_content_area' ); ?>

        </div><!-- END .bloghash-container -->

        <?php do_action( 'bloghash_after_container' ); ?>
        
    <?php endif; ?>
    
    <?php 
    // Възстановяваме оригиналния post
    $post = $original_post;
    wp_reset_postdata();
    ?>

<?php else: ?>
    
    <!-- Стандартен archive template -->
    
    <?php do_action( 'bloghash_before_container' ); ?>

    <div class="bloghash-container">

        <?php do_action( 'bloghash_before_content_area', 'before_post_archive' ); ?>

        <div id="primary" class="content-area">

            <?php do_action( 'bloghash_before_content' ); ?>

            <main id="content" class="site-content" role="main"<?php bloghash_schema_markup( 'main' ); ?>>

                <?php do_action( 'bloghash_content_archive' ); ?>

            </main><!-- #content .site-content -->

            <?php do_action( 'bloghash_after_content' ); ?>

        </div><!-- #primary .content-area -->

        <?php do_action( 'bloghash_sidebar' ); ?>

        <?php do_action( 'bloghash_after_content_area' ); ?>

    </div><!-- END .bloghash-container -->

    <?php do_action( 'bloghash_after_container' ); ?>

<?php endif; ?>

<?php get_footer(); ?>
