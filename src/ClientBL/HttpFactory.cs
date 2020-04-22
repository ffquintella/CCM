using System;
namespace ClientBL
{
    public class HttpFactory
    {
        public static IHttpHandler GetHttpClient()
        {

            var conf = Configurations.Instance;

            if (conf.UseDebugProxy){
                return new HttpClientHandlerProxy();
            }

            return new HttpClientHandler();

        }
    }
}
