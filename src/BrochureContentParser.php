<?php

namespace InvisibleUs\StudyAbroad;

use Symfony\Component\CssSelector\CssSelectorConverter;

class BrochureContentParser {
    public $init = false;
    private $dom = null;
    private $xpath = null;
    private $converter = null;

    private $content = null;

    private $selectors = [
        'description'        => '',
        'images'             => '',
        'video'              => '',
        'sections_container' => '',
        'sections_title'     => '',
        'sections_content'   => '',
    ];

    private $xpath_queries = [
        'description'        => '',
        'images'             => '',
        'video'              => '',
        'sections_container' => '',
        'sections_title'     => '',
        'sections_content'   => '',
    ];

    public $description = '';
    public $images = [];
    public $video_url = '';
    public $sections = [];

    public function __construct(string $content, array $selectors) {
        if (!$content) {
            // Content empty. Nothing to do.
            return;
        }

        $this->content = $content;
        $this->selectors = array_merge($this->selectors, $selectors);
        $this->dom = new \DOMDocument();

        // Supress HTML structure warnings.
        @$success = $this->dom->loadHTML($this->content);
    
        if (!$success) {
            // Loading the content failed. We're done here.
            return;
        }
    
        $this->xpath = new \DOMXPath($this->dom);
        $this->converter = new CssSelectorConverter();
        $this->convert_selectors();
        $this->parse_content();
    }

    private function convert_selectors() {
        foreach ($this->selectors as $key => $selector) {
            $this->xpath_queries[$key] = $this->converter->toXPath($selector);
        }
    }

    public function parse_content() {
        $this->parse_description();
        $this->parse_images();
        $this->parse_video();
        $this->parse_sections();
    }

    public function parse_description() {
        $query = $this->xpath_queries['description'];
        
        if (!$query) {
            return;
        }

        $nodes = $this->xpath->query($query);

        if ($nodes->count()) {
            $el = $nodes->item(0);
            $this->description = $this->normalize_brochure_content($this->dom->saveHTML($el));
        }
    }

    public function parse_images() {
        $query = $this->xpath_queries['images'];
        
        if (!$query) {
            return;
        }

        $img_nodes = $this->xpath->query($query);
        $num_images = $img_nodes->count();

        if (!$num_images) {
            return;
        }

        for ($i=0; $i < $num_images; $i++) {
            $img = $img_nodes->item($i);
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');

            if ($src) {
                $this->process_image($src, $alt);
            }
        }
    }

    public function parse_video() {
        $query = $this->xpath_queries['video'];
        
        if (!$query) {
            return;
        }

        $nodes = $this->xpath->query($query);

        if ($nodes && $nodes->count()) {
            // Trim normal whitespace characters AND non-breaking spaces.
            $url = trim($nodes->item(0)->textContent, " \n\r\t\v\x00\xc2\xa0");
            $url = filter_var($url, FILTER_VALIDATE_URL);

            if (!$url) {
                $atts = ['src', 'href'];

                foreach ($atts as $att) {
                    $url = trim($nodes->item(0)->getAttribute($att));

                    if ($url) {
                        break;
                    }
                }
            }

            if ($url) {
                $this->video_url = $url;
            }
        }
    }

    public function parse_sections() {
        $query = $this->xpath_queries['sections_container'];
        
        if (!$query) {
            return;
        }

        $nodes = $this->xpath->query($query);
        $num_sections = ($nodes) ? $nodes->count() : 0;
        
        if (!$num_sections) {
            return;
        }

        $section_parts = ['title', 'content'];

        for ($i = 0; $i < $num_sections; $i++) {
            $parent_el = $nodes->item($i);
            $section = array_combine(
                $section_parts,
                array_pad([], count($section_parts), '')
            );


            foreach ($section_parts as $part) {
                $query = $this->xpath_queries['sections_' . $part];
                $child_nodes = $this->xpath->query($query, $parent_el);

                if ($child_nodes && $child_nodes->count()) {
                    $value = $this->dom->saveHTML($child_nodes->item(0));
                    $section[$part] = self::normalize_brochure_content($value, $part);
                }
            }

            $this->sections[] = $section;
        }
    }

    private function process_image(string $src, string $alt) {
        $allow_data_uri = apply_filters('nvis/studyabroad/sync_allow_data_uri', false);
        $is_data_uri = strpos($src, 'data:image/') === 0;
        $tdhost = TerraDottaAPI::get_host();

        if (strpos($src, 'http') !== 0 && !$is_data_uri) {
            $src = sprintf('https://%s/%s', $tdhost, $src);
        }

        $is_td_image = !$is_data_uri && strpos($src, $tdhost) !== false;

        if ($is_data_uri) {
            $contents = substr($src, strpos($src,';') + 1);
            $contents = base64_decode($contents);
            $info = getimagesizefromstring($contents);
            unset($contents);
        } else {
            // We know this will produce a warning if the src 404's. Supress the warning.
            $info = @wp_getimagesize($src);
        }

        if (!is_array($info)) {
            // Probably a 404. Bail. 
            return;
        }

        $image = [
            'url' => $src,
            'alt' => $alt,
            'width' => $info[0],
            'height' => $info[1],
        ];

        if ($is_td_image) {
            $src = add_query_arg('type', 'thumb', $src);
            $info = wp_getimagesize($src);

            if (is_array($info)) {
                $image['sizes'] = [
                    'thumbnail' => $src,
                    'thumbnail-width' => $info[0],
                    'thumbnail-height' => $info[1],
                ];
            }
        }

        if (!$is_data_uri || $allow_data_uri) {
            $this->images[] = $image;
        }
    }

    public static function normalize_brochure_content(string $content, string $context='content') {
        // TODO: Add filters for whether or not to strip, and which tags. 
        $content = strip_wrapper_tag($content, []);
    
        switch ($context) {
            case 'title':
                $content = wp_strip_all_tags($content, true);
                $content = trim($content, " \n\r\t\v\x00\xc2\xa0");
                break;
            case 'content':
            default:
                $content = str_replace('<br />', '<br><br>', $content);
                // KSES it to be safe.
                $allowed_html = apply_filters(
                    'nvis/studyabroad/sync_brochure_allowed_html',
                    wp_kses_allowed_html('post')
                );
                $content = wp_kses($content, $allowed_html);
                // Maybe remove style attributes. 
                $strip_styles = apply_filters('nvis/studyabroad/sync_strip_styles', true);
                
                if ($strip_styles) {
                    $content = preg_replace('/ style="([^"])*"/', '', $content);
                }
                // Trim whitespace characters AND non-breaking spaces. 
                $content = trim($content, " \n\r\t\v\x00\xc2\xa0");
                $content = wpautop($content);
                break;
        }
    
        return $content;
    }

}
