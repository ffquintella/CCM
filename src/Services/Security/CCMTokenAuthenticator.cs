using RestSharp;
using RestSharp.Authenticators;

namespace Services.Security
{
    public class CCMTokenAuthenticator: IAuthenticator
    {
        private string Token { get; set; }
        
        public CCMTokenAuthenticator(string token)
        {
            this.Token = token;
        }
        
        public void Authenticate(IRestClient client, IRestRequest request)
        {
            client.AddDefaultHeader("AuthenticationToken", Token);
        }
    }
}