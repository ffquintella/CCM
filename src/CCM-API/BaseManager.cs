using Microsoft.Extensions.Configuration;
using Serilog;


namespace CCM_API
{
    public class BaseManager
    {
        public BaseManager(
            IgniteManager igniteManager,
            IConfiguration configuration )
        {
            this.igniteManager = igniteManager;
            this.configuration = configuration;
        }

        protected readonly IgniteManager igniteManager;
        protected readonly ILogger logger = Log.Logger;
        protected readonly IConfiguration configuration;
    }
}