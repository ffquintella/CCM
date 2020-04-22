using System.Runtime.Loader;
using RestSharp;
using Services.Authentication;
using Services.Security;

namespace Services.Helpers
{
    public static class RestClientHelper
    {
        private static ConfigurationManager ConfigurationManager
        {
            get
            {
                return AppDependencyResolver.Current.GetService<ConfigurationManager>();
            }
        }
        
        private static LoginService LoginService
        {
            get
            {
                return AppDependencyResolver.Current.GetService<LoginService>();
            }
        }
        
        public static RestClient GetClient()
        {
            return GetClient(ConfigurationManager.RestClientBaseUrl, ConfigurationManager.RestClientIgnoreSSL, ConfigurationManager.RestClientApiVersion);
        }
        
        public static RestClient GetAuthenticatedClient()
        {

            var client = GetClient(ConfigurationManager.RestClientBaseUrl, ConfigurationManager.RestClientIgnoreSSL, ConfigurationManager.RestClientApiVersion);
            client.Authenticator = new CCMTokenAuthenticator(LoginService.AuthenticationData.Token);

            return client;
        }

        public static RestClient GetClient(string baseUrl, bool ignoreSSL, string apiVersion)
        {
            if (ignoreSSL)
            {
                var restClient = new RestClient(baseUrl + apiVersion);
                restClient.RemoteCertificateValidationCallback = (sender, certificate, chain, sslPolicyErrors) => true;
                return restClient;
            }
            return new RestClient(baseUrl);
        }
    }
}