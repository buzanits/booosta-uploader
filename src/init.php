<?php
namespace booosta\uploader;

\booosta\Framework::add_module_trait('webapp', 'uploader\webapp');

trait webapp
{
  protected function preparse_uploader()
  {
    $path = 'vendor/npm-asset/dropzone/src';

    if($this->moduleinfo['uploader']):
      $this->add_preincludes("<script type='text/javascript' src='{$this->base_dir}{$path}/dropzone.js'></script>
                           <link rel='stylesheet' href='{$this->base_dir}{$path}/dropzone.scss'>");
    endif;
  }
}
