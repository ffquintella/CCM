using System.Collections.Generic;
using System.Net;
using System.Security.Claims;
using System.Text.Json.Serialization;

namespace Domain.Authentication
{
    public class AuthenticationData
    {

        public AuthenticationData()
        {
            Claims = new List<Claim>();
        }
        
        public AuthenticationStatus Status { get; set; }
        public AuthenticationErrorType ErrorType { get; set; }
        
        public string ErrorMessage { get; set; }
        
        public string Token { get; set; }
        
        public string IpAuthorized { get; set; }
        
        public string Login { get; set; }
        
        [JsonIgnoreAttribute]
        public List<Claim> Claims { get; set; }
        
        public List<string> Roles { get; set; }
    }
}