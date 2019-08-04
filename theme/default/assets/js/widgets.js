/*
 * Copyright (c) 2018 - 2019. karlharris.org
 */

if(typeof Vue === 'function')
{
    widgetElements = document.querySelectorAll('[data-widget]');
    if(null !== widgetElements)
    {
        let widgets = [];
        widgetElements.forEach(function(value)
        {
            if(widgets.indexOf(value.dataset.widget) === -1)
            {
                widgets.push(value.dataset.widget);
            }
        });
        widgets.forEach(function(valueLvl1)
        {
            fetch('/widgets/?load='+valueLvl1).then(response => {
                return response.text();
            }).then(response => {
                widgetElements.forEach(function(valueLvl2)
                {
                    if(valueLvl1 === valueLvl2.dataset.widget)
                    {
                        new Vue({
                            el: '#'+valueLvl2.id,
                            data: {
                                message: ''
                            },
                            created: function()
                            {
                                this.message = response;
                            }
                        });
                    }
                });
            });
        });
    }
}