<?php
/*
 * template : posts with title only
 * 
 * @var array $posts, Object $category, String $layout
 * 
 */
?>
<div class="col-sm-12">
    <div class="white-box hunting-box">
        <h2 class="white-box-heading">
            <?php echo $category->attachment; ?> <?php echo $category->name; ?> 
            <a href="<?php echo get_category_link($category->term_id); ?>" class="see-all-btn pull-right">See All</a>  
        </h2>
        <div class="row hunting-small-box">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $postnumber => $postid): $post = get_post($postid); ?>
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
                            </div>
                        </div>
                    <?php else: ?>    
                        <div class="col-md-3 col-md-6 col-sm-6 hunting-small-box-in">
                            <div class="white-img-box">
                                <a href="<?php echo get_the_permalink($postid); ?>">
                                    <?php echo get_the_post_thumbnail($postid, 'medium'); ?>
                                </a>
                                <p>
                                    <a href="<?php echo get_the_permalink($postid); ?>">
                                        <?php echo $post->post_title; ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>    
                <?php endforeach; ?>    
            <?php endif; ?>
        </div>
    </div>
</div>