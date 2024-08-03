<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GeneratorController extends Controller
{
    public string $data = '';
    public string $tunnel = 'https://';
    public ?string $kontainer;
    private ?array $brands;

    public function __construct(Brand $brand)
    {
        $this->brands = $brand->all()->toArray();
    }

    public function __invoke(Request $request)
    {
        $this->tunnel .= trim($request->tunnel);
        $this->kontainer = trim($request->kontainer);
        if (empty($request->tunnel) || empty($request->tipe)) {
            return 'Error: Tunnel is required';
        }
        if (str_contains($this->tunnel, 'blob.core.windows.net') && empty($this->kontainer)) {
            return 'Error: Kontainer is required for azure storage account blob.core.windows.net';
        }

        if ($request->tipe === 'sitemap') {
            return $this->generateSitemap();
        }
        return $this->generateUrl();
    }

    private function generateSitemap()
    {
        array_walk($this->brands, function ($brand) {
            $filename = Str::slug($brand['name']) . ".html";

            if ($this->kontainer) {
                $this->data .= "
  <url>
    <loc>$this->tunnel/$this->kontainer/$filename</loc>
    <lastmod>".date('Y-m-d')."</lastmod>
  </url>
";
            } else {
                $this->data .= "
  <url>
    <loc>$this->tunnel/$filename</loc>
    <lastmod>".date('Y-m-d')."</lastmod>
  </url>
";
            }
        });

        $xmlStart = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xmlEnd = '</urlset>';

        return response($xmlStart . $this->data . $xmlEnd)->header('Content-Type', 'text/xml');
    }

    private function generateUrl()
    {
        array_walk($this->brands, function ($brand) {
            $filename = Str::slug($brand['name']) . ".html";

            if ($this->kontainer) {
                $this->data .= "$this->tunnel/$this->kontainer/$filename\n";
            } else {
                $this->data .= "$this->tunnel/$filename\n";
            }
        });

        return response($this->data)->header('Content-Type', 'text/plain');
    }
}
