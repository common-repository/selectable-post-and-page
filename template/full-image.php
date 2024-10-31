<?php
/*
 * Full-Image template
 */
$sizes = $pim->get_size( 'spp-widget-feature' ); //400x340
$post_img = $pim->get_image($img, $sizes['width'], $sizes['height']);

//var_dump($post_img);
?>
<div class="spp-clearfix spp-post-full">
    <a href="<?php the_permalink();?>" rel="bookmark" title="<?php the_title_attribute();?>">
        <div class="spp-post-image">
            <?php if (function_exists('the_post_thumbnail') && current_theme_supports("post-thumbnails") && has_post_thumbnail()):?>
                <?php the_post_thumbnail( 'spp-widget-feature' );?>
            <?php elseif (strlen($post_img) > 0):?>
                <img src="<?php echo esc_url($post_img);?>" alt="<?php the_title();?>" title="<?php the_title();?>" />
            <?php endif;?>
        </div>

        <h4 class="spp-post-title"><?php the_title();?></h4>
    </a>
    
    <?php if ( isset( $instance['post_date'] ) ):?>
    <div class="spp-post-meta">
        <?php echo $instance['date_text'];?> 
        <?php the_date('d/m/Y');?>
    </div>
    <?php endif; ?>
        
    <?php if( $instance['excerpt_length'] > 0 ):?>
    <div class="spp-post-content">
        <?php the_excerpt();?> 
    </div>
    <?php endif; ?>
</div>