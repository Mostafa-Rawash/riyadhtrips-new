

(function ($) {



    function checkout_event(event_name) {

        // user data

        let first_name = $(".form-section .form-control[name='first_name']").val()

        let last_name = $(".form-section .form-control[name='last_name']").val()

        let email = $(".form-section .form-control[name='email']").val()

        let phone = $(".form-section .form-control[name='phone']").val()

        // let country = $(".form-section .form-control[name='country'] [selected='selected']").text();

        let country = $(".form-section .form-control[name='country'] option:selected").text();



        let customer_notes = $(".form-section .form-control[name='customer_notes']").val()    

        // booking data

        let start_date = $(".booking-review-content  .review-section:nth-child(2) li:nth-child(1) .val").text().trim()

        let Duration = $(".booking-review-content  .review-section:nth-child(2) li:nth-child(2) .val").text().trim()

        let Adults = $(".booking-review-content  .review-section:nth-child(2) li:nth-child(3) .val").text().trim()

        let Children = $(".booking-review-content  .review-section:nth-child(2) li:nth-child(4) .val").text().trim()

        let Packages = $(".booking-review-content  .review-section:nth-child(2) li:nth-child(5) .val").text().trim()

        let tax = $(".booking-review-content  .review-section:nth-child(3) li:nth-last-child(3) .val").text().trim().replace(/[^0-9,.]/g, '').replace('.', '').replace(',', '.')

        let coupon_code =  $("input[name='coupon_code']").val();

        let payment_method = $("input[name='payment_gateway']:checked").val()

        let service = $(".booking-review .booking-review-content .service-info .service-name a").text()



        let total = $(".booking-review-content .review-section:nth-child(3) li:last-child div .val").text().trim();



        total = total.replace(/[^0-9,.]/g, '').replace('.', '').replace(',', '.')







        window.dataLayer = window.dataLayer || []

        window.dataLayer.push({

            event:event_name,

            "First name" : first_name,    

            "Last name" : last_name,  

            Email : email,  

            Phone : phone,  

            Country : country,  

            "Customer notes" : customer_notes,    

            "Start date" : start_date,    

            Duration : parseFloat(Duration),    

            Adult : parseFloat(Adults),    

            Children : parseFloat(Children),    

            package : Packages,

            Tax : parseFloat(tax),  

            "coupon code" : coupon_code,  

            Total:parseFloat(total),

            "Payment Method":payment_method,

            Currency : currency,

            "Service name" : service

        })    

    }    



      checkout_event("InitiateCheckout");   

    new Vue({

        el:'#bravo-checkout-page',

        data:{

            onSubmit:false,

            message:{

                content:'',

                type:false

            }

        },

        methods:{

            doCheckout(){

                checkout_event("doCheckout");



                var me = this;



                if(this.onSubmit) return false;



                if(!this.validate()) return false;



                this.onSubmit = true;



                $.ajax({

                    url:bookingCore.routes.checkout,

                    data:$('.booking-form').find('input,textarea,select').serialize(),

                    method:"post",

                    success:function (res) {

                        if(!res.status && !res.url){

                            me.onSubmit = false;

                        }





                        if(res.elements){

                            for(var k in res.elements){

                                $(k).html(res.elements[k]);

                            }

                        }



                        if(res.message)

                        {

                            me.message.content = res.message;

                            me.message.type = res.status;

                        }



                        if(res.url){

                            window.location.href = res.url

                        }



                        if(res.errors && typeof res.errors == 'object')

                        {

                            var html = '';

                            for(var i in res.errors){

                                html += res.errors[i]+'<br>';

                            }

                            me.message.content = html;

                        }

                        if(typeof BravoReCaptcha != "undefined"){

                            BravoReCaptcha.reset('booking');

                        }



                    },

                    error:function (e) {

                        me.onSubmit = false;

                        if(e.responseJSON){

							me.message.content = e.responseJSON.message ? e.responseJSON.message : 'Can not booking';

							me.message.type = false;

                        }else{

                            if(e.responseText){

								me.message.content = e.responseText;

								me.message.type = false;

                            }

                        }

                        if(typeof BravoReCaptcha != "undefined"){

                            BravoReCaptcha.reset('booking');

                        }

                    }

                })

            },

            validate(){

                return true;

            }

        },

    })

})(jQuery)

$('#confirmRegister').change(function() {

    if( $(this).prop('checked')) {

       $('#confirmRegisterContent').removeClass('d-none');

    }else {

        $('#confirmRegisterContent').addClass('d-none');

    }

});

