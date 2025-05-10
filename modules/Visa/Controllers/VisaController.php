<?php

namespace Modules\Visa\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Visa\Models\Visa;

class VisaController extends Controller
{
    protected $visaClass;

    public function __construct(Visa $visa)
    {
        $this->visaClass = $visa;
    }

    public function callAction($method, $parameters)
    {
        if(setting_item('visa_disable'))
        {
            return redirect('/');
        }
        return parent::callAction($method, $parameters);
    }

    public function index(Request $request)
    {
        $layout = setting_item("visa_layout_search", 'normal');
        if ($request->query('_layout')) {
            $layout = $request->query('_layout');
        }
        $is_ajax = $request->query('_ajax');

        if(!empty($request->query('limit'))){
            $limit = $request->query('limit');
        }else{
            $limit = !empty(setting_item("visa_page_limit_item"))? setting_item("visa_page_limit_item") : 9;
        }
        
        $query = $this->visaClass->search($request->input());
        $list = $query->paginate($limit);

        $data = [
            'rows' => $list,
            'layout' => $layout
        ];
        
        if ($is_ajax) {
            return $this->sendSuccess([
                'fragments' => [
                    '.ajax-search-result' => view('Visa::frontend.ajax.search-result', $data)->render(),
                    '.result-count' => $list->total() ? ($list->total() > 1 ? __(":count visas found",['count'=>$list->total()]) : __(":count visa found",['count'=>$list->total()])) : '',
                    '.count-string' => $list->total() ? __("Showing :from - :to of :total Visas",["from"=>$list->firstItem(),"to"=>$list->lastItem(),"total"=>$list->total()]) : ''
                ]
            ]);
        }
        
        $data = [
            'rows' => $list,
            "blank" => setting_item('search_open_tab') == "current_tab" ? 0 : 1,
            "seo_meta" => [
                'title' => __('Visa Applications'),
                'description' => __('Your visa applications'),
                'keywords' => __('visa,application'),
            ],
            'layout' => $layout
        ];
        
        return view('Visa::frontend.search', $data);
    }

    public function detail(Request $request, $id)
    {
        $row = $this->visaClass::findOrFail($id);
        
        $data = [
            'row' => $row,
            'page_title' => __('Visa Application #:code', ['code' => $row->unique_code]),
            'breadcrumbs' => [
                [
                    'name' => __('Visa Applications'),
                    'url' => route('visa.search'),
                ],
                [
                    'name' => __('Application #:code', ['code' => $row->unique_code]),
                    'class' => 'active'
                ]
            ],
            'body_class' => 'is_single'
        ];
        
        return view('Visa::frontend.detail', $data);
    }
}