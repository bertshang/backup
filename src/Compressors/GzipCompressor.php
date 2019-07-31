<?php

namespace Bertshang\Backup\DbDumper\Compressors;

class GzipCompressor implements Compressor
{
    public function useCommand(): string
    {
        return 'gzip';
    }

    public function useExtension(): string
    {
        return 'gz';
    }
}
