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
    
    // Вземаме body classes на страницата
    $page_classes = get_body_class('', $custom_page_id);
    
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
    if (!$is_elementor_canvas && $sidebar_position !== 'no-sidebar' && !in_array('bloghash-no-sidebar', $page_classes)) {
        $has_sidebar = true;
    }
    
    // Добавяме класове за да симулираме страница вместо category
    $wrapper_classes = array('category-as-page-wrapper');
    
    // Копираме важните класове от страницата
    foreach ($page_classes as $class) {
        if (strpos($class, 'bloghash-') === 0 || strpos($class, 'elementor') === 0) {
            $wrapper_classes[] = $class;
        }
    }
    ?>
    
    <style>
    /* Премахваме category page header */
    body.category .bloghash-page-header {
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
    
    /* Важно: Презаписваме body classes за да работят CSS правилата на страницата */
    .category-as-page-wrapper.bloghash-no-sidebar #primary {
        max-width: 100% !important;
    }
    
    .category-as-page-wrapper.bloghash-sidebar-style-2 #primary {
        max-width: 70%;
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
    </style>
    
    <?php
    // Категорийна информация
    $category = get_queried_object();
    if ($category && isset($category->name) && !$is_elementor_canvas) : 
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
        
        <!-- Elementor Canvas - пълна ширина -->
        <div class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>">
            <?php echo apply_filters('the_content', $custom_page->post_content); ?>
        </div>
        
    <?php else: ?>
        
        <!-- Стандартен BlogHash layout с класове от страницата -->
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