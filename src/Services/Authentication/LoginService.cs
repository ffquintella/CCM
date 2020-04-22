using System.Net;
using Domain.Authentication;
using RestSharp;
using Serilog;
using Services.Helpers;

namespace Services.Authentication
{
    public class LoginService
    {

        protected readonly ILogger logger = Log.Logger;
        private RestClient client;

        public AuthenticationData AuthenticationData { get; set; }
        public LoginService()
        {
            client = RestClientHelper.GetClient();
        }
        
        public bool IsLoggedIn { get; set; }

        public void Logout()
        {
            IsLoggedIn = false;
        }
        
        public void ExecuteLogin(string login, string password)
        {
            // /Authentication/Authenticate
            var client = RestClientHelper.GetClient();
            
            var request = new RestRequest("/Authentication/Authenticate");

            var authRequest = new AuthenticationRequest()
            {
                Login = login,
                Password = password
            };

            request.AddJsonBody(authRequest);

            var result = client.Post<AuthenticationData>(request);

            if (result.StatusCode == HttpStatusCode.OK && result.Data.Status == AuthenticationStatus.OK)
            {
                this.AuthenticationData = result.Data;
                this.IsLoggedIn = true;
                logger.Information("Login OK account:{0}", login);
            }
            else
            {
                if (result.IsSuccessful) this.AuthenticationData = result.Data;
                this.IsLoggedIn = false;
                logger.Warning("Login failed account:{0}", login);
            }

        }
        
        public void ExecuteLogout()
        {
            this.IsLoggedIn = false;
        }
        
    }
}