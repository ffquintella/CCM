using Microsoft.Extensions.Configuration;
using Serilog;

namespace Services
{
    public class ConfigurationManager
    {
        public string RestClientBaseUrl { get; set; }
        public bool RestClientIgnoreSSL { get; set;  }
        
        public string RestClientApiVersion { get; set;  }

        public void LoadConfiguration(IConfiguration configuration)
        {
            RestClientBaseUrl = configuration["ccm-api:baseUrl"];
            RestClientIgnoreSSL = bool.Parse(configuration["ccm-api:ignoreSSL"]);
            RestClientApiVersion = configuration["ccm-api:apiVersion"];
        }
    }
}