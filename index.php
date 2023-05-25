<?php

if (!defined('ABSPATH')) exit;

class TheGemHeadingAnimation {

    private static $instance = null;

    public $activeAnimations;
    public $rotatingTextEnabled;

    const ANIMATION_LINES_SLIDE_UP = 'lines-slide-up';
    const ANIMATION_LINES_SLIDE_UP_RANDOM = 'lines-slide-up-random';
    const ANIMATION_WORDS_SLIDE_UP = 'words-slide-up';
    const ANIMATION_WORDS_SLIDE_LEFT = 'words-slide-left';
    const ANIMATION_WORDS_SLIDE_RIGHT = 'words-slide-right';
    const ANIMATION_LETTERS_SLIDE_UP = 'letters-slide-up';
    const ANIMATION_LETTERS_SCALE_OUT = 'letters-scale-out';
    const ANIMATION_TYPEWRITER = 'typewriter';
    const ANIMATION_BACKGROUND_SLIDING = 'background-sliding';
    const ANIMATION_FADE_TB = 'fade-tb';
    const ANIMATION_FADE_BT = 'fade-bt';
    const ANIMATION_FADE_LR = 'fade-lr';
    const ANIMATION_FADE_RL = 'fade-rl';
    const ANIMATION_FADE_SIMPLE = 'fade-simple';

    public static function instance() {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return self::$instance;
    }

    public static function getAnimationList() {
        return [
            static::ANIMATION_LINES_SLIDE_UP => __('Lines Slide Up', 'thegem'),
            static::ANIMATION_LINES_SLIDE_UP_RANDOM => __('Lines Slide Up Random', 'thegem'),
            static::ANIMATION_WORDS_SLIDE_UP => __('Words Slide Up', 'thegem'),
            static::ANIMATION_WORDS_SLIDE_LEFT => __('Words Slide Left', 'thegem'),
            static::ANIMATION_WORDS_SLIDE_RIGHT => __('Words Slide Right', 'thegem'),
            static::ANIMATION_LETTERS_SLIDE_UP => __('Letters Slide Up', 'thegem'),
            static::ANIMATION_LETTERS_SCALE_OUT => __('Letters Scale Out', 'thegem'),
            static::ANIMATION_TYPEWRITER => __('Typewriter', 'thegem'),
            static::ANIMATION_BACKGROUND_SLIDING => __('Background sliding', 'thegem'),
            static::ANIMATION_FADE_TB => __('Fade top to bottom', 'thegem'),
            static::ANIMATION_FADE_BT => __('Fade bottom to top', 'thegem'),
            static::ANIMATION_FADE_LR => __('Fade left to right', 'thegem'),
            static::ANIMATION_FADE_RL => __('Fade right to left', 'thegem'),
            static::ANIMATION_FADE_SIMPLE => __('Simple fade', 'thegem')
        ];
    }

    public static function getDefaultInterval($animation) {
        $data = [
            static::ANIMATION_LINES_SLIDE_UP=>80,
            static::ANIMATION_LINES_SLIDE_UP_RANDOM=>160,
            static::ANIMATION_WORDS_SLIDE_UP =>60,
            static::ANIMATION_WORDS_SLIDE_LEFT=>20,
            static::ANIMATION_WORDS_SLIDE_RIGHT=>15,
            static::ANIMATION_LETTERS_SLIDE_UP=>15,
            static::ANIMATION_TYPEWRITER=>30,
            static::ANIMATION_LETTERS_SCALE_OUT=>30
        ];

        return isset($data[$animation]) ? $data[$animation] : 0;
    }

    public function init() {
		wp_register_style('thegem-heading-animation', plugin_dir_url(__FILE__).'assets/css/main.css', []);
		wp_register_script('thegem-heading-main', plugin_dir_url(__FILE__).'assets/js/main.js');
		wp_register_script('thegem-heading-prepare-animation', plugin_dir_url(__FILE__).'assets/js/prepare-animation.js');
		wp_register_script('thegem-heading-rotating', plugin_dir_url(__FILE__).'assets/js/rotating.js');

		if (is_singular()) {
			$elementor_data = get_post_meta(get_the_ID(), '_elementor_data');

			if ($elementor_data) {
				if (is_array($elementor_data)) {
					$elementor_data = $elementor_data[0];
				}
				$data = json_decode($elementor_data, true);
				$elements = array();
				\Elementor\Plugin::$instance->db->iterate_data( $data, function( $element ) use ( &$elements ) {
					$elements[] = $element;
				} );

				if (!empty($elements)) {
					foreach($elements as $element) {
						if (isset($element['elType']) && $element['elType'] == 'widget') {
							if ($element['widgetType'] == 'thegem-animated-heading') {
								$headingSettings = $element['settings'];
								foreach ($headingSettings['text_content'] as $content) {
									if (isset($content['rotating_text_enabled'])) {
										$this->rotatingTextEnabled = true;
									}
								}
								if (isset($headingSettings['heading_animation'])) {
									$this->activeAnimations[] = $headingSettings['heading_animation'];
								} else {
									$this->activeAnimations[] = static::getDefaultAnimation();
								}

								if (!empty($this->activeAnimations)) {
									$this->includeAssets();
								}
							}
						}
					}
				}
			}
		}
    }

    public static function getDefaultAnimation() {
        return static::ANIMATION_LINES_SLIDE_UP;
    }

    private function isPrepareAnimation() {
        if (empty($this->activeAnimations)) return false;
        $prepareAnimations = [static::ANIMATION_LINES_SLIDE_UP, static::ANIMATION_LINES_SLIDE_UP_RANDOM];
        return !empty(array_intersect($prepareAnimations, $this->activeAnimations));
    }

    private function includeAssets() {
        wp_enqueue_style('thegem-heading-animation');

        if (is_user_logged_in()) {
            wp_enqueue_script('thegem-heading-main');

            if ($this->isPrepareAnimation()) {
                wp_enqueue_script('thegem-heading-prepare-animation');
            }

            if ($this->rotatingTextEnabled) {
                wp_enqueue_script('thegem-heading-rotating');
            }
        }
    }

    public function includeInlineJs() {
        if (is_user_logged_in() || empty($this->activeAnimations)) return;

        static $isIncludeInlineJs;

        if (!$isIncludeInlineJs) {
            if ($js = file_get_contents(plugin_dir_path(__FILE__).'assets/js/main.js')) {

                if ($this->isPrepareAnimation()) {
                    $js .= file_get_contents(plugin_dir_path(__FILE__).'assets/js/prepare-animation.js') ? file_get_contents(plugin_dir_path(__FILE__).'assets/js/prepare-animation.js') : '';
                }

                if ($this->rotatingTextEnabled) {
                    $js .= file_get_contents(plugin_dir_path(__FILE__).'assets/js/rotating.js') ? file_get_contents(plugin_dir_path(__FILE__).'assets/js/rotating.js') : '';
                }

                $js = preg_replace('/(\s{2,})/', '', $js);
                $js = str_replace(["\r\n", "\r", "\n"], '',  $js);
                echo "<script type=\"text/javascript\">$js</script>";
            }

            $isIncludeInlineJs = true;
        }
    }

    public static function parse($heading_text, $params, &$index, $inner_class, $text_style) {
        $text = '';

        $heading_text = preg_replace('/&nbsp;+/', ' ', htmlentities($heading_text, null, 'UTF-8'));
        $heading_text = html_entity_decode($heading_text);

        $animation_interval = !empty($params['heading_animation_interval']['size']) ? (int)$params['heading_animation_interval']['size'] : TheGemHeadingAnimation::getDefaultInterval($params['heading_animation']);
        $animation_delay = !empty($params['heading_animation_delay']['size']) ? (int)$params['heading_animation_delay']['size'] : 0;

        if (in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP_RANDOM, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_LEFT, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_RIGHT, TheGemHeadingAnimation::ANIMATION_LETTERS_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_TYPEWRITER, TheGemHeadingAnimation::ANIMATION_LETTERS_SCALE_OUT])) {
            $inner_class .= ' thegem-heading-word';
        }

        if (in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP_RANDOM, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_LEFT, TheGemHeadingAnimation::ANIMATION_WORDS_SLIDE_RIGHT])) {
            $text .= preg_replace_callback('/(\S+)/u', function ($matches) use (&$index, $inner_class, $text_style, $params, $animation_interval, $animation_delay) {
                $index++;

                if (!in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP_RANDOM])) {
                    $text_style .= ' animation-delay: '.($animation_delay + ($animation_interval * $index)).'ms';
                }

                $html = '<span'.(!empty($inner_class) ? ' class="'.esc_attr(trim($inner_class)).'"' : '').(!empty($text_style) ? ' style="'.esc_attr(trim($text_style)).'"' : '').'>'.$matches[1].'</span>';

                if (!in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_LINES_SLIDE_UP_RANDOM])) {
                    $html = '<span class="thegem-heading-word-wrap">'.$html.'</span>';
                }

                return $html;

            }, $heading_text);
        }

        if (in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_LETTERS_SLIDE_UP, TheGemHeadingAnimation::ANIMATION_TYPEWRITER, TheGemHeadingAnimation::ANIMATION_LETTERS_SCALE_OUT])) {
            $text .= preg_replace_callback('/(\S+)/u', function ($matches) use (&$index, $inner_class, $text_style, $params, $animation_interval, $animation_delay) {
                $word = preg_replace_callback('/(\S)/u', function ($matches) use (&$index, $inner_class, $text_style, $params, $animation_interval, $animation_delay) {
                    $index++;
                    $style = 'animation-delay: '.($animation_delay + ($animation_interval * $index)).'ms';
                    $html = '<span class="thegem-heading-letter" style="'.$style.'">'.$matches[1].'</span>';
                    if ($params['heading_animation'] == TheGemHeadingAnimation::ANIMATION_LETTERS_SLIDE_UP) {
                        $html = '<span class="thegem-heading-letter-wrap">'.$html.'</span>';
                    }
                    return $html;
                }, $matches[1]);

                return '<span'.(!empty($inner_class) ? ' class="'.esc_attr(trim($inner_class)).'"' : '').(!empty($text_style) ? ' style="'.esc_attr(trim($text_style)).'"' : '').'>'.$word.'</span>';

            }, $heading_text);
        }

        if (in_array($params['heading_animation'], [TheGemHeadingAnimation::ANIMATION_BACKGROUND_SLIDING, TheGemHeadingAnimation::ANIMATION_FADE_TB, TheGemHeadingAnimation::ANIMATION_FADE_BT, TheGemHeadingAnimation::ANIMATION_FADE_LR, TheGemHeadingAnimation::ANIMATION_FADE_RL, TheGemHeadingAnimation::ANIMATION_FADE_SIMPLE])) {
            $text .= '<span'.(!empty($inner_class) ? ' class="'.esc_attr(trim($inner_class)).'"' : '').(!empty($text_style) ? ' style="'.esc_attr(trim($text_style)).'"' : '').'>'.$heading_text.'</span>';
        }

        return $text;
    }

}