using System.Net;

namespace CCM_API.Helpers
{
    public static class TokenHelper
    {
        public static string GenerateToken(IPAddress ipAddress, string login)
        {
            var entropy = RandomHelper.RandomString(30);
            var key = login + ":" + ipAddress.ToString() + ":" + entropy;
            
            return StringHelper.ConvertToBase64(key);
        }
    }
}