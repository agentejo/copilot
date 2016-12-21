
<ul  class="uk-breadcrumb">
    @render('copilot:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Files')</span></li>
</ul>

<div riot-view>

    <div class="uk-tab-center">
        <ul class="uk-tab" ref="tab">
            <li class="uk-active"><a>Site</a></li>
            <li><a>Content</a></li>
            <li><a>Theme</a></li>
            <li><a>Menu</a></li>
        </ul>
    </div>

    <ul id="finders" class="uk-switcher uk-margin-large-top">
        <li>
            <cp-finder path="site"></cp-finder>
        </li>
        <li>
            <cp-finder path="content"></cp-finder>
        </li>
        </li>
        <li>
            <cp-finder path="site/theme"></cp-finder>
        </li>
        <li>
            <cp-finder path="site/menu"></cp-finder>
        </li>
    </ul>

    <script type="view/script">

        console.log(this.refs)

        this.on('mount', function(){
            
            UIkit.tab(this.refs.tab, {connect:'#finders'});
        });

    </script>

</div>
