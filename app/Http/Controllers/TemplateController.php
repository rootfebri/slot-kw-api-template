<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    public array $brands;
    public string $logo = 'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/polisi-slot.png';
    public array $gambar = [
        'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/59.webp',
        'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/63.webp',
        'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/64.webp',
        'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/65.webp',
        'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/hq720.webp',
    ];

    public string $link = 'https://pub-b6838d4bc267444aa80f65a77404eef4.r2.dev/amp1.html';
    public array $templates = ['slot3','slot4','slot5'];

    public function __construct(Brand $brand, public ?string $kontainer = '', public ?string $tunnel = '', public ?string $data = '')
    {
        $this->brands = $brand->all()->toArray();
    }

    public function validate(Request $request)
    {
        $request->validate([
            'tunnel' => ['required', 'regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i'],
            'kontainer' => ['required_if:tunnel,==,blob.core.windows.net','required_if:tunnel,==,storage.sgp.cloud.ovh.net'],
        ]);
    }

    public function __invoke(Request $request)
    {
        if ($request->method() === 'GET') {
            return $this->get();
        }
        return $this->post($request);
    }

    public function get()
    {
        return view('TemplateGenerator');
    }

    public function post(Request $request)
    {
        $rand_color = function (): string {
            return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        };
        $this->validate($request);
        $link = $this->link;
        $folder = base_path("../tunnel-html/$request->tunnel");

        if ($request->kontainer) {
            $host = trim($request->tunnel) . "/$request->kontainer";
        } else {
            $host = trim($request->tunnel);
        }
        $this->kontainer = $request->kontainer;
        $this->tunnel = 'https://' . $request->tunnel;

        $mkd = @mkdir($folder);
        if (!$mkd) {
            `mkdir $folder -p`;
        }
        if ($request->gverify) {
            if (str_contains($request->gverify, ',')) $gverifies = explode(',', $request->gverify);
            else $gverifies = [$request->gverify];
            foreach ($gverifies as $gverify) {
                file_put_contents("$folder/$gverify.html", 'google-site-verification: google7ccb62e6c181cc97.html');
            }
        }
        file_put_contents("$folder/robots.txt", "User-agent: *\nAllow: /\nSitemap:https://$host/sitemap.xml");
        $sitemap = file_put_contents("$folder/sitemap.xml", $this->generateSitemap());
        echo $sitemap;

        foreach ($this->brands as $brand) {
            $brand = $brand['name'];
            $randomTemplate = array_rand($this->templates);
            $selectedTemplate = $this->templates[$randomTemplate];

            $randomImage = array_rand($this->gambar);
            $gambarTerpilih = $this->gambar[$randomImage];

            $fileName = Str::slug($brand) . ".html";
            $logo = $this->logo;
            $content = view($selectedTemplate, compact('brand', 'gambarTerpilih', 'host', 'link', 'logo', 'rand_color'));
            if ($request->debug) {
                return view('slot5', compact('brand', 'gambarTerpilih', 'host', 'link', 'logo'));
            }
            file_put_contents("$folder/$fileName", $content);
        }
        return '<div style="display: flex; justify-items: center; justify-content: center; font-size: large"><button onclick="history.back()">Done &amp; Go Back</button></div>';
    }

    private function generateSitemap()
    {
        array_walk($this->brands, function ($brand) {
            $filename = Str::slug($brand['name']) . ".html";

            if ($this->kontainer) {
                $this->data .= "
  <url>
    <loc>$this->tunnel/".request()->kontainer."/$filename</loc>
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

        return $xmlStart . $this->data . $xmlEnd;
    }
}
