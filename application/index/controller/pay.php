<?php
    public function sign(array $data, $key)
    {
        array_filter($data);
        ksort($data);
        return strtoupper(md5(urldecode(http_build_query($data).'&key='.$key)));
    }
