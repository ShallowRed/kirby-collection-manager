<?php

return function(...$data) {

  extract($data);

  // Get the configured pagination parameter name
  $paginationParam = $config['pagination']['param'] ?? 'p';

  return A::merge($data, compact('pagination'));
};
