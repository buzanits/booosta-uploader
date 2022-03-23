<?php
namespace booosta\uploader;

use \booosta\Framework as b;
b::init_module('uploader');

class Uploader extends \booosta\ui\UI
{
  use moduletrait_uploader;

  protected $action;
  protected $hidden = [];
  protected $allowedfiles = '';
  protected $thumb_width, $thumb_height;
  protected $message;
  protected $timeout;  // in seconds!
  protected $max_filesize;
  protected $show_formtags = true;
  protected $in_form;
  protected $multiple = true;

  protected $before_upload, $after_upload;
  protected $sending_multiple, $success_multiple;
  protected $sending, $success;

  public function __construct($name = null, $action = '', $in_form = false)
  {
    parent::__construct();
    $this->id = "uploader$name";
    $this->name = $name;
    $this->action = $action;
    $this->in_form = $in_form;
  }

  public function after_instanciation()
  {
    parent::after_instanciation();

    if(is_object($this->topobj) && is_a($this->topobj, "\\booosta\\webapp\\Webapp")):
      $this->topobj->moduleinfo['uploader'] = true;
    endif;
  }

  public function set_action($action) { $this->action = $action; }
  public function set_message($message) { $this->message = $message; }
  public function set_timeout($timeout) { $this->timeout = $timeout; }
  public function set_max_filesize($max_filesize) { $this->max_filesize = $max_filesize; }
  public function set_multiple($multiple) { $this->multiple = $multiple; }
  public function add_hidden($name, $value) { $this->hidden[$name] = $value; }
  public function set_allowedfiles($files) { $this->allowedfiles = $files; }
  public function add_allowedfiles($file) { $this->allowedfiles .= $file; }
  public function set_thumb_size($width, $height) { $this->thumb_width = $width; $this->thumb_height = $height; }
  public function before_upload($code) { $this->before_upload = $code; }
  public function after_upload($code) { $this->after_upload = $code; }
  public function sending($code) { $this->sending= $code; }
  public function success($code) { $this->success= $code; }
  public function sending_multiple($code) { $this->sending_multiple = $code; }
  public function success_multiple($code) { $this->success_multiple = $code; }
  public function show_formtags($code) { $this->show_formtags = $code; }


  public function get_htmlonly() 
  { 
    $param = '';

    if($this->in_form):
      return "<div id='$this->id' class='dropzone'> <div class='dz-default dz-message'><span>$this->message</span></div></div>";
    else:
      foreach($this->hidden as $name=>$value) $param .= "<input type='hidden' name='$name' value='$value'>";
      if($this->message) $param .= "<div class='dz-message' data-dz-message><span>$this->message</span></div>";

      return "<form action='$this->action' class='dropzone' id='$this->id'> <div class='fallback'>
              <input name='$this->name' type='file' multiple /> </div> $param </form>";
    endif;
  }

  public function get_js() 
  { 
    $params = 'maxThumbnailFilesize: 50, ';
    $initcode = '';

    if($this->allowedfiles) $params .= "acceptedFiles: '$this->allowedfiles', ";
    if($this->thumb_width) $params .= "thumbnailWidth: $this->thumb_width, thumbnailHeight: $this->thumb_height, thumbnailMethod: 'contain', ";
    if($this->timeout) $params .= "timeout: {$this->timeout}000, ";
    if($this->max_filesize) $params .= "maxFilesize: $this->max_filesize, ";
    if($this->before_upload) $params .= "this.on('addedfile', function(file) { $this->before_upload } ); ";
    if($this->after_upload) $params .= "queuecomplete: function() { $this->after_upload }, ";
    if(!$this->multiple) $params .= 'maxFiles: 1, ';

    if($this->in_form):
      $hiddenvars = http_build_query($this->hidden);

      $multiple = $this->multiple ? 'true' : 'false';
      $params .= "uploadMultiple: $multiple, parallelUploads: 3, ";

      if($this->sending) $initcode .= "this.on('sending', function() { $this->sending }); ";
      if($this->success) $initcode .= "this.on('success', function(file, response) { $this->success }); ";
      if($this->sending_multiple) $initcode .= "this.on('sendingmultiple', function() { $this->sending_multiple }); ";
      if($this->success_multiple) $initcode .= "this.on('successmultiple', function(files, response) { $this->success_multiple }); ";
      if($this->error_multiple) $initcode .= "this.on('errormultiple', function(files, response) { $this->error_mulitple }); ";

      $js = "
        $('#$this->id').dropzone({ url: '$this->action?$hiddenvars', addRemoveLinks: true, paramName: '$this->name', $params
          init: function() { $initcode },
          success: function (file, response) { file.previewElement.classList.add('dz-success'); },
          error: function (file, response) { file.previewElement.classList.add('dz-error'); } });";

      if(method_exists($this->parentobj, 'add_jquery_ready')) $this->parentobj->add_jquery_ready($js);
      else return "\$(document).ready(function(){ $js });";

      return 'Dropzone.autoDiscover = false;';
    else:
      return "Dropzone.options.$this->id = { paramName: '$this->name', $params };"; 
    endif;
  }
}
