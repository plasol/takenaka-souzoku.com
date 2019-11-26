<?php if(have_posts()): while(have_posts()): the_post(); ?>
	<li>
  	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <?php the_content(); ?>
	</li>
<?php endwhile; endif; ?>