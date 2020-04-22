namespace Domain.Security
{
    public class AllAccessClaim: BaseClaim
    {
        public AllAccessClaim() : base("AllAccess") { }
    }
}