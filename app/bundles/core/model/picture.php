<?php
namespace Core\Model;
require_once LIBRARY . '/Upload/class.upload.php';

class Picture extends \ActiveRecord\Model
{
    static $PATH      = '';
    static $ORIGINALS = ORIGINALS;    
    static $WWW_PATH  = '/var/www/needsandfun.ru/current/uploads/pics';    

    const MIN_X = 450;
    const MIN_Y = 290;
    
    static $sizes = array(
        'originals'     => array(800, 800),
        'full'          => array(455, 455),
        'image'         => array(500, 500),
        'big_banner'    => array(640, 260),
        'today_banner'  => array(250, 150),
        'place'         => array(455, 290), 
        'event'         => array(455, 290),
        'article'       => array(455, 290),
        'middle'        => array(300, 300),
        'small_event'   => array(210, 130),
        'small_article' => array(210, 130),
        'small'         => array(150, 150),
        'shop_banner'   => array(150, 130),
        'smaller_event' => array(147, 90),        
        'brand_preview' => array(140, 100),
        'friend_preview' => array(140, 100),
        'place_banner'  => array(130, 130),
        'thumb'         => array(100, 100),
        'icon'          => array(50, 50)
    );

    static $belongs_to = array(
        array('good')
    );
    
    public function get_path()
    {
        return self::$WWW_PATH . '/' . $this->filename;
    }
    
    public function get_icon()
    {
        return self::$WWW_PATH . '/icon/' . $this->filename;    
    } 
    
    static function upload($file)
    {
        $result = false; 
        $image = self::process($file);
        
        if ($image && !empty($image->processed))
        {
            $picture           = new self();
            $picture->filename = $image->file_dst_name;
            $picture->save();
            $result = $picture;
        }
        
        return $result;
    }
    
    static function resize($file, $type) 
    {
        $options = array(
            'file'      => $file,
            'type'      => $type,
            'keep_name' => true
        );

        return self::process($options);
    }
    
    static function process($options) 
    {
        $result = false;

        $file = is_array($options)
            ? $options['file']
            : $options;

        if (!is_array($file))
        {
            $file = self::$ORIGINALS . '/' . $file;
            $check = false;
        }
        else
        {
            $check = true;
        }


        $type = empty($options['type'])
            ? false
            : $options['type'];

        $keep_name = !empty($options['keep_name'])
            ? $options['keep_name']
            : true;

        $min_x = !empty($options['min_x'])
            ? $options['min_x']
            : self::MIN_X;

        $min_y = !empty($options['min_y'])
            ? $options['min_y']
            : self::MIN_Y;            
        
        $image = new \Upload($file);

        if (!$image->error) 
        {
            if (    $check && 
                    ($image->image_src_x < $min_x 
                  || $image->image_src_y < $min_y))
            {
              //  $page = \Core\Page::get();
              //  $message = 'Ошибка! ' . $image->file_src_name . ' не был загружен, т.к. ширина или высота меньше ' . $min_x . 'x' . $min_y . ' пикселей';
              //  $page->setMessage($message);
                
              //  $min_x = $image->image_src_x;
             //   $min_y = $image->image_src_y;
                
            }
            
            //else 
           // {

                if (isset(self::$sizes[$type]))
                {
                    list($width, $height) = self::$sizes[$type];
                }               


                if (!$keep_name)
                {
                    $filename = md5(date('U'));            
                    $image->file_new_name_body = $filename;            
                }
                
                // save uploaded image with no changes
                $image->allowed            = 'image/*';
                $image->overwrite          = true;
                
                if (!empty($width) && !empty($height))
                {
                    $image->image_resize       = true;
                  //  $image->image_ratio_crop   = true;
                    if($image->image_src_x > $image->image_src_y) {
                      $image->image_ratio_y         = true;
                    }
                    else {
                      $image->image_ratio_x         = true;
                    }
                   // else { $image->image_ratio_crop   = true; }
                }

                if (!empty($width))
                {
                    $image->image_x = $width;
                }

                if (!empty($height))
                {
                    $image->image_y = $height;
                }
                

                
                if (in_array($image->file_src_mime, array('image/jpg', 'image/jpeg', 'image/pjpeg')))
                {
                    $image->quality = 90;    
                }
                
                
                
                
                $path = $type 
                    ? (self::$PATH . '/' . $type)
                    : self::$ORIGINALS;
                    
                $image->Process($path);
                
                if ($image->processed) 
                {
                    return $image;
                } 
          //  }
        }

        if ($image->error)
        {
            echo 'Error: ' . $image->error;
            die();
        }
        
        return $result;        
    }
    
    static function multipleUpload($files, \Core\Model\Itemswithpics $item, \Core\Model\Picsforitem $picture)
    {
        foreach($files['name'] as $key => $val)
        {
            $file = array(
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error'    => $files['error'][$key],
                'size'     => $files['size'][$key]
            );
            
            if ($file['size'])
            {                
                $options = array(
                    'file'  => $file
                );

                if (!empty($picture::$_WIDTH))
                {
                    $options['min_x'] = $picture::$_WIDTH;
                }

                if (!empty($picture::$_HEIGHT))
                {
                    $options['min_y'] = $picture::$_HEIGHT;
                }
                

                $image = self::upload($options);
                
                if ($image && !empty($image->id))
                {
                    $picture             = $picture::create();
                    $picture->picture_id = $image->id;                    
                    $picture->item_id    = $item->getBindId();
                    $picture->weight     = $item->getMaxPicWeight();                    
                    $picture->save();
                }
            }
        }    
    }
}
