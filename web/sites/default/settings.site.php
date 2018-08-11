<?php

$settings['trusted_host_patterns'] = array(
  '^gendered_drupal\.test$',
  '^genderedtextproject\.lndo\.site$',
  '^genderedtextproject\.com$',
  '^.+-gendered.pantheonsite.io$',
);

$settings['hash_salt'] = 'dDcHZCdnyNTj8xLQDGwTkX7wuIlCn29I2bZI15A5wzlstWRR1VXiyQboqUom1_xIlv9W-14PpA';

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => 'sites/default/config',
);

$settings['file_private_path'] = 'sites/default/files/private';