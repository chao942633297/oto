<?php
namespace Vendor\AliPay;
class Config
{
    public static function config()
    {
        return $config = array(
            //应用ID,您的APPID。
            'app_id' => "2017090608579748",

            //商户私钥，您的原始格式RSA私钥
            'merchant_private_key' => "MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBAMvF5nj5FbT4DxuYkGU1f1GCwM7AnbO3jd79WM6v7iHnFE6TsxT17lOBb3b4OcItlTGZYoJyRiTUzJIC88f2DYEIZW0Q1z1oPG4TNGT5qtG8Tt9jjXOIWCmumpG230e0QLSw8hGIMpVKe6U1lwVlBW7E9Cu3G6FH7MhODU0o8FpRAgMBAAECgYBUaSpLuoLvcEJx4AUQp9SR42QTQV8Sb1rpoHEFTYOLr7mNw0lPyYBsPxX5ZmImudMvtKZF+yhrYWtMoMRzdM2qhATKzqDttxoNpP2cdnBALt7LEs2eCqvjFVIRii/OP2EmnIqg4WyQGzM8VlQtKmxjbR01nqAcxhIx3WGLXy4mcQJBAPeNrGdK/XRfMX+tDmq3hKoeHous1x6Vqq9AieYjTE4TXjTy8X3u0L1GF5ydM9t7PBeMHct22s0ze7MSr+p8NXsCQQDSudCMKse9xvAD8FWnFXzU8bTLua1FV7JSZf+Nbj0dFUEU5f2jxKPGn7yoBV2Gu+T2Ms6NyDMDusVZM8oV0NejAkEAxzJB+y/1eLGsw97+DdM0NitcSuo+g4bNPI4DKKNYoC6njJW8yqfjYZpIH6bDdqXYOd5ujy1JbPszW2n7EgT2hwJBANDsCSfvAjnVkwFUtpcBZtJ5EZcqb+/gEpw/JC9ErLK4792YaPCFdRroJFMxfpimkUrG9KL9aha4hhD6l9EpTaECQQDMFznx3O0Wi7fVFDHEADK8mw998DnwdM8fnh6hUvVHeRdt1LWkeQDsErPqABK48CtJBSC0lUzboH8bxPBhYjqS",

            //异步通知地址
            'notify_url' => "http://rj.runjiaby.com/home/Not/aliYiNotify",

            //同步跳转
            'return_url' => 'http://h.runjiaby.com/paySuccess.html',

            //编码格式
            'charset' => "UTF-8",

            //签名方式
            'sign_type' => "RSA",

            //支付宝网关
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

            //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
            'alipay_public_key'=>"MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB",


        );

    }
}