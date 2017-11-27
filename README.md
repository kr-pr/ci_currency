This code provides a render function to enable multicurrency dispaly in e-commerce app based on CodeIgniter framework. It can be used throughout your php views by replacing instances of echo $cart->getTotal() with echo $this->currencies->render_price($cart->getTotal())

The currency selctor is rendered with:
echo $this->currencies->render_currency_toggle("cart")
echo $this->currencies->render_currency_selector("cart")

a pair of toggle and selector should have same string ID passed as argument.