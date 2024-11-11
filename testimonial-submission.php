<?php
/*
Plugin Name: Testimonial Submission
Description: A plugin to create a "Testimonials" custom post type with a front-end submission form.
Version: 1.0
Author: Abhishek
*/

// Register the Custom Post Type
function ts_register_testimonial_post_type() {
    $args = array(
        'label'               => 'Testimonials',
        'public'              => true,
        'has_archive'         => true,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'comments' ),
        'capability_type'     => 'post',
        'show_in_rest'        => true,
    );
    register_post_type( 'testimonial', $args );
}
add_action( 'init', 'ts_register_testimonial_post_type' );

// Shortcode for Front-End Submission Form
function ts_testimonial_submission_form() {
    // Check if the user is logged in
    if ( ! is_user_logged_in() ) {
        // Display JavaScript to show pop-up when form is submitted by non-logged-in users
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                const form = document.querySelector(".testimonial-form");
                form.addEventListener("submit", function(event) {
                    alert("Please log in to submit a testimonial.");
                    event.preventDefault(); // Prevent form submission
                });
            });
        </script>';
    }

    // Form submission logic for logged-in users
    if ( isset( $_POST['ts_testimonial_submit'] ) && is_user_logged_in() ) {
        if ( ! isset( $_POST['ts_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['ts_testimonial_nonce'], 'ts_testimonial_submission' ) ) {
            return 'Sorry, your nonce did not verify.';
        }

        $title = sanitize_text_field( $_POST['ts_testimonial_title'] );
        $content = sanitize_textarea_field( $_POST['ts_testimonial_content'] );

        // Insert the testimonial post
        $testimonial_data = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'testimonial',
            'post_status'  => 'pending',
        );
        wp_insert_post( $testimonial_data );

        echo 'Thank you for your testimonial! It is pending review.';
    }

    ob_start(); ?>
    <style>
        .testimonial-form {
            max-width: 600px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .testimonial-form label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        .testimonial-form input[type="text"], .testimonial-form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .testimonial-form input[type="submit"] {
            margin-top: 15px;
            padding: 10px 15px;
            background: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

    <form method="post" class="testimonial-form">
        <label for="ts_testimonial_title">Title</label>
        <input type="text" name="ts_testimonial_title" id="ts_testimonial_title" required>

        <label for="ts_testimonial_content">Your Testimonial</label>
        <textarea name="ts_testimonial_content" id="ts_testimonial_content" rows="5" required></textarea>

        <?php wp_nonce_field( 'ts_testimonial_submission', 'ts_testimonial_nonce' ); ?>
        <input type="submit" name="ts_testimonial_submit" value="Submit">
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode( 'ts_testimonial_form', 'ts_testimonial_submission_form' );

// Shortcode to Display Approved Testimonials
function ts_display_approved_testimonials() {
    $args = array(
        'post_type'      => 'testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query( $args );
    ob_start();

    if ( $query->have_posts() ) :
        echo '<div class="testimonials">';
        while ( $query->have_posts() ) : $query->the_post(); ?>

            <div class="testimonial-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <h2><?php the_title(); ?></h2>
                <div><?php the_content(); ?></div>
            </div>
        <?php endwhile;
        echo '</div>';
    else :
        echo 'No testimonials found.';
    endif;

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'ts_display_testimonials', 'ts_display_approved_testimonials' );
    