<?php
/*
 * List-View template
 */
?>
<div class="spp-clearfix spp-post-default">
    <a href="<?php the_permalink();?>" rel="bookmark" title="<?php the_title_attribute();?>">
        <h4 class="spp-post-title"><?php the_title();?></h4>
    </a>
    
    <?php if ( isset( $instance['post_date'] ) ):?>
    <div class="spp-post-meta">
        <?php echo $instance['date_text'];?> 
        <?php the_date('d/m/Y');?>
    </div>
    <?php endif; ?>
        
    <?php if ( $instance['excerpt_length'] > 0 ):?>
    <div class="spp-post-content">
        <?php the_excerpt();?> 
    </div>
    <?php endif; ?>
</div>