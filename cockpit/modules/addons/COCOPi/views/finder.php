
<ul  class="uk-breadcrumb">
    @render('cocopi:views/partials/subnav.php')
    <li><span class="uk-text-primary">@lang('Finder')</span></li>
</ul>

<div riot-view>

    <div class="uk-tab-center">
        <ul name="tab" class="uk-tab">
            <li class="uk-active"><a>Site</a></li>
            <li><a>Content</a></li>
            <li><a>Theme</a></li>
            <li><a>Menu</a></li>
        </ul>
    </div>

    <ul id="finders" class="uk-switcher uk-margin-large-top">
        <li>
            <cp-finder root="/site"></cp-finder>
        </li>
        <li>
            <cp-finder root="/content"></cp-finder>
        </li>
        </li>
        <li>
            <cp-finder root="/site/theme"></cp-finder>
        </li>
        <li>
            <cp-finder root="/site/menu"></cp-finder>
        </li>
    </ul>

    <script type="view/script">

        this.on('mount', function(){
            UIkit.tab(this.tab, {connect:'#finders'});
        });

    </script>

</div>
