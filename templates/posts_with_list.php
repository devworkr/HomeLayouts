<?php
/*
 * template : posts with title only
 * 
 * @var array $posts, Object $category, String $layout
 * 
 */
?>
<div class="col-sm-12">
    <div class="white-box fishing-box">
        <h2 class="white-box-heading">
            <?php echo $category->attachment; ?> <?php echo $category->name; ?> 
            <a href="<?php echo get_category_link($category->term_id); ?>" class="see-all-btn pull-right">See All</a>  
        </h2>
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $postnumber => $postid): $post = get_post($postid); setup_postdata($post); ?>
                <?php if ($postnumber == 0): ?>
                    <div class="col-md-12">
                        <div class="white-img-box">
                            <a href="<?php echo get_the_permalink($postid); ?>">
                                <?php echo get_the_post_thumbnail($postid, 'large'); ?>
                            </a>
                            <h3>
                                <a href="<?php echo get_the_permalink($postid); ?>">
                                    <?php echo $post->post_title; ?>
                                </a>
                            </h3>
                            <p></p>
                            <p><?php  echo get_the_excerpt();  ?></p>
                            <p></p>
                        </div>
                    </div>
                <?php else: ?>    
                    <div class="col-sm-12 fishing-thumb-box">
                        <div class="row">
                            <div class="col-sm-5">
                                <a href="<?php echo get_the_permalink($postid); ?>">
                                    <?php echo get_the_post_thumbnail($postid, 'medium'); ?>
                                </a>
                            </div>
                            <div class="col-sm-7">
                                <h3>
                                    <a href="<?php echo get_the_permalink($postid); ?>">
                                        <?php echo $post->post_title; ?>
                                    </a>
                                </h3>
                                <p></p>
                                <p><?php  echo get_the_excerpt();  ?></p>
                                <p></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>    
            <?php endforeach; //wp_reset_postdata(); ?>    
        <?php endif; ?>
    </div>
</div>